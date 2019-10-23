<?php

namespace Yoga\Console\Command;

/**
 * @Command("api-documentation", description = "Generate API blueprint and compile it into public_http/var/api-doc/index.html")
 */
class ApiDocumentation extends \Yoga\Console\Command {

    public function handle() {
        if (!$this->ensureDocDir()) {
            return;
        }
        $this->generateApiBlueprint();
        $this->renderHtml();
    }

    private function ensureDocDir() {
        $apiDocumentationDirectory = $this->getApiDocumentationDirectory();
        if (!$apiDocumentationDirectory) {
            return false;
        }
        if (!file_exists($apiDocumentationDirectory)) {
            mkdir($apiDocumentationDirectory);
        }
        chdir($apiDocumentationDirectory);
        return true;
    }

    private function generateApiBlueprint() {
        file_put_contents(
            $this->getApiDocumentationDirectory() . $this->getBlueprintFilename(),
            $this->getApiBlueprintMarkdown()
        );
    }

    private function getApiBlueprintMarkdown() {
        return $this->getHeaderMarkdown() .
            $this->getGroupsMarkdown();
    }

    private function renderHtml() {
        shell_exec('aglio -i ' . $this->getBlueprintFilename() . ' -o index.html');
        echo \Yoga\Configuration::service()->getHttpHost()
            . '/var/api-documentation/' . PHP_EOL;
    }

    private function getApiDocumentationDirectory() {
        return \Yoga\ComputeOnce::service()->handle(function () {
            $publicHttpVarDirectory = \Yoga\Application::service()->getRootDirectory()
                . 'public_http/var/';
            if (!file_exists($publicHttpVarDirectory)) {
                return null;
            }
            return $publicHttpVarDirectory . 'api-documentation/';
        });
    }

    private function getBlueprintFilename() {
        return 'api-blueprint.md';
    }

    private function getHeaderMarkdown() {
        return 'FORMAT: 1A'
            . PHP_EOL . 'HOST: ' . \Yoga\Configuration::service()->getHttpHost() . '/api'
            . PHP_EOL
            . PHP_EOL . '# API'
            . PHP_EOL
            . PHP_EOL;
    }

    private function getGroupsMarkdown() {
        $apis = \Yoga\Api\Reflection\Reader::service()->getReflections();
        $groups = \Yoga\Console\Command\ApiDocumentation\ApiGrouper::service()
            ->groupApis($apis);
        $result = '';
        foreach ($groups as $group) {
            $result .= PHP_EOL . PHP_EOL . '# Group ' . $group->getNamespace();
            $description = '';
            $result .= PHP_EOL . $description;
            foreach ($group->getRoutes() as $route) {
                $urlPattern = $route->getUrlPattern();
                $routeLabel = (count($route->getApis()) > 1)
                    ? substr($urlPattern, 5)
                    : $route->getApis()[0]->getClassWithoutNamespace();
                $urlPatternInfo = \Yoga\Console\Command\ApiDocumentation\UrlPatternParser::service()
                    ->parse($urlPattern);
                $result .= PHP_EOL . PHP_EOL . '## ' .
                    $routeLabel .
                    ' [' . $urlPatternInfo->getUrlPatternClarified() . ']' . PHP_EOL;
                $description = '';
                foreach ($urlPatternInfo->getParameterConstraints() as $name => $value) {
                    $description .= PHP_EOL . '`' . $name . '` in the URL must match `' . $value . '`';
                }
                $result .= PHP_EOL . $description;
                foreach ($route->getApis() as $api) {
                    $method = $api->getMethod();
                    $class = $api->getClass();
                    $description = 'PHP class: `\\' . $class . '`'
                        . PHP_EOL . PHP_EOL . 'Javascript handle: `' . str_replace('\\', '.', $class) . '`';
                    $comment = $api->getComment();
                    if ($comment) {
                        $description .= PHP_EOL . PHP_EOL . str_replace(PHP_EOL, '  ' . PHP_EOL, $comment);
                    }
                    if (is_array($method)) {
                        $description .= PHP_EOL . PHP_EOL . 'Other methods supported: ' .
                            implode(', ', array_slice($method, 1));
                        $method = $method[0];
                    }
                    $result .= PHP_EOL . PHP_EOL . '### ' . $api->getClassWithoutNamespace() . ' [' . $method . ']';
                    $result .= PHP_EOL . $description;
                    $parameters = $api->getParameters();
                    $maxLineLength = 0;
                    if ($parameters) {
                        $lines = [];
                        $comments = [];
                        $n = count($parameters);
                        for ($i = 0; $i < $n; $i++) {
                            $parameter = $parameters[$i];

                            if (!$parameter->getType()) {
                                $type = 'mixed';
                            } else {
                                $type = $parameter->getType()->getName();
                            }
                            if ($parameter->getIsArray()) {
                                $type .= '[]';
                            }
                            $line = '"' . $parameter->getName() . '": ' . $type;
                            if ($i < $n - 1) {
                                $line .= ',';
                            }
                            $lines[] = $line;
                            $lineLength = strlen($line);
                            if ($lineLength > $maxLineLength) {
                                $maxLineLength = $lineLength;
                            }
                            $comment = '';
                            foreach ($parameter->getAttributes() as $name => $value) {
                                if ($comment) {
                                    $comment .= ', ';
                                }
                                $comment .= $name . ' = ' . var_export($value, true);
                            }
                            if ($parameter->getComment()) {
                                if ($comment) {
                                    $comment .= '. ';
                                }
                                $comment .= $parameter->getComment();
                            }
                            $comments[] = $comment;
                        }
                        for ($i = 0; $i < $n; $i++) {
                            if (!$comments[$i]) {
                                continue;
                            }
                            $lines[$i] .= str_repeat(' ', $maxLineLength - strlen($lines[$i]))
                                . '    // ' . $comments[$i];
                        }
                        $result .= PHP_EOL
                            . PHP_EOL . '+ Request'
                            . PHP_EOL
                            . PHP_EOL . '        {'
                            . implode(PHP_EOL . '            ', array_merge([''], $lines))
                            . PHP_EOL . '        }';
                    }
                    $result .= PHP_EOL . PHP_EOL . $this->getResponseMarkdown($api);
                }
            }
        }
        return $result;
    }

    private function getResponseMarkdown(\Yoga\Api\Reflection $api) {
        $lines = array_merge(
            $this->getResponseMarkdownLinesFromAnnotations($api),
            $this->getResponseMarkdownLinesFromTests($api)
        );
        if (!$lines) {
            $lines = $this->getResponseMarkdownNoData($api);
        }
        return '+ Response 200'
            . PHP_EOL
            . implode(PHP_EOL . '        ', array_merge([''], $lines));
        return $result;
    }

    private function getResponseMarkdownLinesFromAnnotations(\Yoga\Api\Reflection $api) {
        $handleMethod = $this->getHandleMethodReflection($api);
        $docComment = $handleMethod->getDocComment();
        preg_match('/@return\s*(\S+)/', $docComment, $matches);
        if (2 != count($matches)) {
            return [];
        }
        $returnClass = $matches[1];
        $isArray = (substr($returnClass, -2) == '[]');
        if ($isArray) {
            $returnClass = substr($returnClass, 0, -2);
        }
        if (class_exists($returnClass)) {
            $lines = \Yoga\Formatter::service()->mergeLinesAndComments(
                $this->getMarkdownForClass($returnClass, $isArray)
            );
        } else {
            $lines = [$returnClass];
        }
        return array_merge(
            ['From @return annotation on `\\' . $api->getClass() . '::handle()`:'],
            [''],
            $lines
        );
    }

    private function getMarkdownForClass($class, $isArray, $level = 0) {
        $reflection = \Yoga\Reflection\Reader::service()
            ->getReflection($class);
        $result = [];
        $indentationOne = '    ';
        $indentationFull = str_repeat($indentationOne, $level);
        foreach ($reflection->getProperties() as $property) {
            $line = $indentationFull . $indentationOne . $property->getName() . ': ';
            $comment = $property->getComment();
            $isObject = (\Yoga\Enum\PropertyType::OBJECT == $property->getType()->getValue());
            if ($isObject) {
                $isTime = '\\' . \Yoga\DateTime::class == $property->getType()->getPropertyClass();
                $isEnum = !$isTime && is_subclass_of(
                    $property->getType()->getPropertyClass(),
                    \Yoga\Enum::class
                );
                if ($isTime || $isEnum) {
                    $line .= $property->getType()->getPropertyClass();
                } else {
                    $objectMarkdown = $this->getMarkdownForClass(
                        $property->getType()->getPropertyClass(),
                        $property->getIsArray(),
                        $level + 1
                    );
                    $line .= trim($objectMarkdown[0][0]);
                    $comment = $objectMarkdown[0][1] . ($comment ? ' - ' . $comment : '');
                    array_splice($objectMarkdown, 0, 1);
                }
            } else {
                $line .= $property->getType()->getName();
                if ($property->getIsArray()) {
                    $line .= '[]';
                }
            }
            if ($result) {
                $result[count($result) - 1][0] .= ',';
            }
            $result[] = [$line, $comment];
            if ($isObject && !$isTime && !$isEnum) {
                $result = array_merge($result, $objectMarkdown);
            }
        }
        $result = array_merge(
            [[$indentationFull . '{', $class]],
            $result,
            [[$indentationFull . '}', null]]
        );
        if ($isArray) {
            foreach ($result as &$a) {
                $a[0] = $indentationOne . $a[0];
            }
            $result[count($result) - 1][0] .= ',';
            $result = array_merge(
                [[$indentationFull . '[', null]],
                $result,
                [[$indentationFull . $indentationOne . '...', null]],
                [[$indentationFull . ']', null]]
            );
        }
        return $result;
    }

    /**
     * @param \Yoga\Api\Reflection $api
     * @return \ReflectionMethod
     */
    private function getHandleMethodReflection(\Yoga\Api\Reflection $api) {
        $reflectionClass = new \ReflectionClass($api->getClass());
        return $reflectionClass->getMethod('handle');
    }

    private function getResponseMarkdownLinesFromTests(\Yoga\Api\Reflection $api) {
        $result = [];
        $endpointResults = \Yoga\Test\Documentor::service()
            ->restoreEndpointResults($api->getClass());
        foreach ($endpointResults as $endpointResult) {
            $expectedResult = $endpointResult->getExpectedResult();
            $result = array_merge(
                $result,
                [''],
                [
                    'From `' . $endpointResult->getTestMethod() . '()` in `'
                    . $endpointResult->getTestFilePath() . '` at line '
                    . $endpointResult->getTestFileLineNumber() . ':'
                ],
                [''],
                \Yoga\Pickler::service()
                    ->getReadableJsonLines($expectedResult)
            );
        }
        return $result;
    }

    private function getResponseMarkdownNoData(\Yoga\Api\Reflection $api) {
        $handleMethod = $this->getHandleMethodReflection($api);
        $warning = 'WARNING: no tests with `\Yoga\Test::assertApiResponse()` for this API, and no `@return` annotation in `\\' . $api->getClass() . '::handle()`.';
        if ($api->getClass() != $handleMethod->getDeclaringClass()->name) {
            return [$warning];
        }
        $fileName = $handleMethod->getFileName();
        $fileName = substr($fileName, 1 + strpos($fileName, '/server/Api/'));
        return [
            $warning,
            '',
            '    /**',
            '      * @return \Namespace\ReturnClassName <-- add this to ' .
            $fileName . ', line ' .
            ($handleMethod->getStartLine()),
            '      */',
            '    public function handle() {',
            '    ...'
        ];
    }

}