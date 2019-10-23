<?php

namespace Yoga\Api\Reflection;

/**
 * @method static Reader service()
 */
class Reader extends \Yoga\Service {

    /**
     * @return \Yoga\Api\Reflection[]
     */
    public function getReflections() {
        return \Yoga\ComputeOnce::service()->handle(function () {
            $result = [];
            $apiDirectory = \Yoga\Application::service()->getRootDirectory() .
                'server/Api/';
            if (!file_exists($apiDirectory)) {
                return $result;
            }
            $reflections = \Yoga\DirectoryReader::service()->getReflections($apiDirectory, 'Api');
            foreach ($reflections as $reflection) {
                $result[$reflection->getName()] = $this->getReflection($reflection->getName());
            }
            return $result;
        });
    }

    /**
     * @param string $apiClass
     * @throws \Exception
     * @return \Yoga\Api\Reflection
     */
    public function getReflectionByApiClass($apiClass) {
        $reflections = $this->getReflections();
        if (!isset($reflections[$apiClass])) {
            throw new \Exception('Reflection not found for API `' . $apiClass . '`');
        }
        return $reflections[$apiClass];
    }

    /**
     * @param string $class
     * @throws \Exception
     * @return \Yoga\Api\Reflection
     */
    private function getReflection($class) {
        $reflection = \Yoga\Reflection\Reader::service()
            ->getReflection($class);
        $result = (new \Yoga\Api\Reflection)
            ->setClass($class)
            ->setComment($reflection->getComment())
            ->setParameters($reflection->getProperties());
        foreach ($reflection->getAnnotations() as $annotation) {
            if ($annotation->getName() == 'Route') {
                $arguments = $annotation->getArguments();
                if (!isset($arguments[0])) {
                    throw new \Exception(
                        'Bad API annotation for `' . $class .
                        '` - define path pattern in `@Route(<path/pattern>)`'
                    );
                }
                $result->setUrlPattern($arguments[0]);
                if (!isset($arguments['method'])) {
                    throw new \Exception(
                        'Bad API annotation for `' . $class .
                        '` - define method in `@Route(<urlPattern>, method="<method>")`'
                    );
                }
                $result->setMethod($arguments['method']);
                if (isset($arguments['permissionRequired'])) {
                    $result->setPermissionRequired($arguments['permissionRequired']);
                    $result->setIsLoginRequired(true);
                } elseif (isset($arguments['isLoginRequired'])) {
                    $result->setIsLoginRequired($arguments['isLoginRequired']);
                }
            }
        }
        preg_match_all('/{([\w\d]+)[:}]/', $result->getUrlPattern(), $matches);
        $routeParameters = [];
        foreach ($matches[1] as $parameterName) {
            $routeParameters[] = $parameterName;
        }
        $result->setRouteParameters($routeParameters);
        return $result;
    }

}