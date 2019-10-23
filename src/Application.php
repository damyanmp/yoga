<?php

namespace Yoga;

/**
 * @method static Application service()
 */
class Application extends Service {

    public function bootstrap($isTest = false) {
        $this->bootstrapConfiguration($isTest);
        DateTime::setUtc();
        Logger::service()->setDirectory($this->getRootDirectory() . 'var/log/');
        $this->bootstrapErrorLogging();
        return $this;
    }

    private function bootstrapConfiguration($isTest) {
        if ($isTest) {
            $configurationFilename = $this->getRootDirectory() . 'tests/Configuration.php';
            if (file_exists($configurationFilename)) {
                require_once $configurationFilename;
                \Yoga\Configuration::substitute('\Tests\Configuration');
                if (class_exists('\Configuration')) {
                    \Configuration::substitute('\Tests\Configuration');
                }
                if (class_exists('\ConfigurationBase')) {
                    \ConfigurationBase::substitute('\Tests\Configuration');
                }
                return;
            }
        }
        \Yoga\Configuration::substitute(\Yoga\Configuration\Override::class);
        if (class_exists('\Configuration')) {
            \Configuration::substitute(\Yoga\Configuration\Override::class);
        }
        if (class_exists('\ConfigurationBase')) {
            \ConfigurationBase::substitute(\Yoga\Configuration\Override::class);
        }
    }

    public function handle() {
        try {
            $result = $this->handleRequest();
            ob_end_flush();
            if (!headers_sent()) {
                $this->echoResult($result);
            }
            return;
        } catch (\Yoga\Api\Exception $e) {
            $errorCode = $e->getErrorCode();
            $errorDescription = $e->getMessage();
            $httpCode = $e->getCode();
        } catch (\Exception $e) {
            $errorCode = null;
            $errorDescription = $e->getMessage();
            Logger::service()->debug($errorDescription . "\n" . $e->getTraceAsString());
            $httpCode = \Yoga\Enum\HttpResponseCode::INTERNAL_SERVER_ERROR;
        }
        header($errorDescription, true, $httpCode);
        $this->echoResult([
            'errorCode' => $errorCode,
            'errorDescription' => $errorDescription
        ]);
    }

    /**
     * @return string
     */
    public function getRootDirectory() {
        return ComputeOnce::service()->handle(function () {
            return realpath(__DIR__ . '/../../../../') . '/';
        });
    }

    private function handleRequest() {
        if (Arrays::service()->safeGet($_SERVER, 'REQUEST_METHOD') == 'OPTIONS') {
            return;
        }
        $requestUrl = Arrays::service()->safeGet($_SERVER, 'REDIRECT_URL');
        // compiled javascripts are generated on-the-fly in dev environment
        if (substr($requestUrl, 0, 8) == '/var/js/') {
            header('Content-Type: application/javascript');
            echo
                \Yoga\Compiler\Javascript::service()
                    ->getCompiledResult($requestUrl);
            return;
        }
        // some urls are reserved by the framework
        if ($requestUrl == Session::RESERVED_URL_LOGGED_IN) {
            return Session::service()->loggedIn();
        }
        if ($requestUrl == Session::RESERVED_URL_LOGOUT) {
            return Session::service()->logout();
        }

        // Phalcon router
        $router = $this->getPhalconRouter();
        // API Reflections (meta data from the endpoints defined in annotations)
        $apiReflections = \Yoga\Api\Reflection\Reader::service()->getReflections();

        // Feed route information from the API Reflections to Phalcon Router
        foreach ($apiReflections as $reflection) {
            // Prepend all route parameters with `p_`, because Phalcon is weird with some reserved names like `action`
            $urlPattern = preg_replace(
                '/{([\w\d]+)([:}])/',
                '{p_$1$2',
                $reflection->getUrlPattern()
            );
            $a = ['class' => $reflection->getClass()];
            foreach ($reflection->getRouteParameters() as $parameterName) {
                $a['p_' . $parameterName] = count($a);
            }
            $router->add($urlPattern, $a, $reflection->getMethod());
        }

        // Let Phalcon Router handle the request - it will be matched against
        // the route patterns defined in the APIs
        $router->handle();
        $routingResult = $router->getParams();

        if (!isset($routingResult['class'])) {
            throw new \Yoga\Api\Exception(
                'Not found',
                \Yoga\Enum\HttpResponseCode::NOT_FOUND
            );
        }

        $class = $routingResult['class'];
        $reflection = $apiReflections[$class];

        $request = $this->getPhalconRequest();

        if ($reflection->getIsLoginRequired()) {
            $accessToken = $this->getParameterRawValue(
                'accessToken',
                $request,
                $routingResult
            );
            Sso::service()->assertAccessTokenValid($accessToken, $reflection->getPermissionRequired());
        }

        /** @var Api $api */
        $api = new $class;

        // Now we zero in on the specific endpoint and validate/inject its parameters
        foreach ($reflection->getParameters() as $parameter) {
            $parameterName = $parameter->getName();
            $parameterRawValue = $this->getParameterRawValue(
                $parameterName,
                $request,
                $routingResult
            );
            $validator = \Yoga\Api\Validator::factory(
                $parameter->getType(),
                $parameter->getIsArray(),
                $parameter->getAttributes()
            );
            if ($validator) {
                $parameterValue = $validator
                    ->setParameterName($parameterName)
                    ->handle($parameterRawValue);
            } elseif ($parameter->getType()->getValue() == \Yoga\Enum\PropertyType::BOOLEAN) {
                $parameterValue = $parameterRawValue && ($parameterRawValue != 'false');
            } else {
                $parameterValue = $parameterRawValue;
            }
            $api->$parameterName = $parameterValue;
        }

        return $api->handle();
    }

    /**
     * @param string $parameterName
     * @param \Phalcon\Http\Request $request
     * @param array $routingResult
     * @return mixed
     */
    private function getParameterRawValue(
        $parameterName,
        \Phalcon\Http\Request $request,
        array $routingResult
    ) {
        if (isset($routingResult['p_' . $parameterName])) {
            return urldecode($routingResult['p_' . $parameterName]);
        }
        if ($request->has($parameterName)) {
            return $request->get($parameterName);
        }
        return Arrays::service()
            ->safeGet($this->getPhpInput(), $parameterName);
    }

    /**
     * @return array
     */
    private function getPhpInput() {
        return ComputeOnce::service()->handle(function () {
            return json_decode(file_get_contents('php://input'), true);
        });
    }

    private function echoResult($result) {
        if ($result instanceof \Yoga\Response\FileInMemory) {
            header('Content-Type: ' . $result->getType()->getHttpResponseType());
            header('Content-Disposition: attachement; filename="' . $result->getName() . '";');
            echo $result->getContent();
        } else {
            header('Content-Type: application/json');
            echo Api::convertResponseToJson($result);
        }
    }

    /**
     * @return \Phalcon\DI
     */
    private function getPhalconDi() {
        return ComputeOnce::service()->handle(function () {
            return new \Phalcon\DI;
        });
    }

    /**
     * @return \Phalcon\Http\Request
     */
    private function getPhalconRequest() {
        return ComputeOnce::service()->handle(function () {
            return new \Phalcon\Http\Request;
        });
    }

    /**
     * @return \Phalcon\Mvc\Router
     */
    private function getPhalconRouter() {
        return ComputeOnce::service()->handle(function () {
            $di = $this->getPhalconDi();
            $di['request'] = $this->getPhalconRequest();
            $router = new \Phalcon\Mvc\Router(false);
            $router->setDI($di);
            $router->setUriSource(\Phalcon\Mvc\Router::URI_SOURCE_SERVER_REQUEST_URI);
            return $router;
        });
    }

    private function bootstrapErrorLogging() {
        ini_set('log_errors', 1);
        ini_set('error_log', \Yoga\Logger::service()->getDebugLogFullPath());
        error_reporting(E_ALL);
        set_error_handler(function ($errorCode, $errorMessage) {
            // error was suppressed with the @-operator
            if (0 === error_reporting()) {
                return false;
            }
            \Yoga\Logger::service()->debug(
                \Yoga\Enum\PhpError::getConstants()[$errorCode] . ': ' . $errorMessage,
                null,
                true,
                2,
                true,
                2
            );
        });
    }

}
