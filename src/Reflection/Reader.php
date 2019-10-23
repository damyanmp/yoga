<?php

namespace Yoga\Reflection;

/**
 * @method static Reader service()
 */
class Reader extends \Yoga\Service {

    /**
     * @param string $class
     * @return \Yoga\Reflection
     */
    public function getReflection($class) {
        $result = (new \Yoga\Reflection);
        $this->readAnnotations($class, $result);
        $this->readComment($class, $result);
        $this->readParameters($class, $result);
        return $result;
    }

    private function readAnnotations($class, \Yoga\Reflection $result) {
        $annotationsReader = $this->getAnnotationsReader();
        $reflector = $annotationsReader->get($class);
        $classAnnotations = $reflector->getClassAnnotations();
        $annotations = [];
        if ($classAnnotations) {
            foreach ($classAnnotations as $classAnnotation) {
                $annotations[] = (new Annotation)
                    ->setName($classAnnotation->getName())
                    ->setArguments($classAnnotation->getArguments());
            }
        }
        $result->setAnnotations($annotations);
    }

    private function readComment($class, \Yoga\Reflection $result) {
        $reflectionClass = new \ReflectionClass($class);
        $result->setComment(
            $this->extractCommentFromDocComment(
                $reflectionClass->getDocComment()
            )
        );
    }

    private function extractCommentFromDocComment($docComment) {
        $lines = explode(PHP_EOL, str_replace("\r", '', $docComment));
        $goodLines = [];
        $n = count($lines);
        for ($i = 1; $i < $n - 1; $i++) {
            $line = $lines[$i];
            if (preg_match('/^\s*\*\s(\s*[^\s@].*)/', $line, $matches)) {
                $goodLines[] = $matches[1];
            }
        }
        return implode(PHP_EOL, $goodLines);
    }

    private function readParameters($class, \Yoga\Reflection $result) {
        $properties = [];
        $reflectionClass = new \ReflectionClass($class);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $name = $reflectionProperty->getName();
            $docComment = $reflectionProperty->getDocComment();
            preg_match("/@var ([^\n]+)/", $docComment, $matches);
            if (!isset($matches[1])) {
                throw new \Exception(
                    'Bad API annotation for `' . $class . '.' . $name .
                    '` - define `@var <type>(<properties>)`'
                );
            }
            $typeAndProperties = $matches[1];
            if ($p = strpos($typeAndProperties, '(')) {
                $type = substr($typeAndProperties, 0, $p);
                $parametersString = substr($typeAndProperties, $p + 1);
                if (!($p = strrpos($parametersString, ')'))) {
                    throw new \Exception(
                        'Bad API annotation for `' . $class . '.' . $name .
                        '` - properties must be enclosed in brackets'
                    );
                }
                $parametersString = substr($parametersString, 0, $p);
            } else {
                $type = $typeAndProperties;
                $parametersString = null;
            }
            $attributes = [];
            if ($parametersString) {
                $parameterStrings = \Yoga\Strings::service()->parseCsvLine($parametersString);
                foreach ($parameterStrings as $parameterString) {
                    $a = explode('=', $parameterString);
                    if (count($a) != 2) {
                        throw new \Exception(
                            'Bad API annotation for `' . $class . '.' . $name .
                            '` - cannot parse out property name/value from `' .
                            $parameterString . '`'
                        );
                    }
                    $parameterName = trim($a[0]);
                    $attributes[$parameterName] = $this->getParameterValue(trim($a[1]), $class);
                }
            }
            $type = trim($type);
            if ('array' == strtolower($type)) {
                $isArray = true;
                $type = null;
            } else {
                $isArray = (substr($type, -2) == '[]');
                if ($isArray) {
                    $type = substr($type, 0, -2);
                }
                $type = \Yoga\Enum\PropertyType::createFromAnnotationTypeString($type);
                if (
                    \Yoga\Enum\PropertyType::OBJECT == $type->getValue()
                    && !class_exists($type->getPropertyClass())
                ) {
                    $type->setPropertyClass(
                        '\\' . $reflectionClass->getNamespaceName() . '\\'
                        . $type->getPropertyClass()
                    );
                }
            }
            $properties[] = (new \Yoga\Reflection\Property)
                ->setName($reflectionProperty->getName())
                ->setType($type)
                ->setIsArray($isArray)
                ->setAttributes($attributes)
                ->setComment($this->extractCommentFromDocComment($docComment));
        }
        $result->setProperties($properties);
    }

    /**
     * @return \Phalcon\Annotations\Adapter\Memory
     */
    private function getAnnotationsReader() {
        return \Yoga\ComputeOnce::service()->handle(function () {
            return new \Phalcon\Annotations\Adapter\Memory;
        });
    }

    private function getParameterValue($parameterString, $class) {
        $parameterString = trim($parameterString);
        if (substr($parameterString, 0, 1) == '"' && substr($parameterString, -1) == '"') {
            return substr($parameterString, 1, strlen($parameterString) - 2);
        }
        $lower = strtolower($parameterString);
        if ($lower == 'true') {
            return true;
        }
        if ($lower == 'false') {
            return false;
        }
        if ($lower == 'null') {
            return null;
        }
        $intval = intval($parameterString);
        if (strval($intval) === $parameterString) {
            return $intval;
        }
        $floatval = floatval($parameterString);
        if (strval($floatval) === $parameterString) {
            return $floatval;
        }
        throw new \Exception(
            'Bad class meta data... cannot parse `' . $parameterString . '` in `' . $class . '`'
        );
    }

}