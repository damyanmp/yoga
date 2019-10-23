<?php

namespace Yoga;

/**
 * @method static Pickler service()
 */
class Pickler extends Service {

    /**
     * @param mixed $variable
     * @param boolean $isClassNameRequired
     * @return array
     */
    public function pickle(
        $variable,
        $isClassNameRequired = true
    ) {
        return $this->doPickle($variable, $isClassNameRequired);
    }

    /**
     * @param array $pickle
     * @return mixed
     */
    public function unpickle($pickle) {
        return $this->doUnpickle($pickle);
    }

    public function pickleToBase64String($variable) {
        return base64_encode(json_encode($this->pickle($variable)));
    }

    public function unpickleFromBase64String($base64string) {
        return $this->unpickle(json_decode(base64_decode($base64string), true));
    }

    /**
     * @param mixed $variable
     * @return string[]
     */
    public function getReadableJsonLines($variable) {
        $pickle = $this->pickle($variable, true);
        return Formatter::service()->mergeLinesAndComments(
            $this->getReadableJsonLinesAndComments($pickle)
        );
    }

    private function getReadableJsonLinesAndComments($variable, $level = 0) {
        $isArray = is_array($variable);
        $indentationOne = '    ';
        $indentationAll = str_repeat($indentationOne, $level);
        if ($isArray) {
            $isObject = $isArray && isset($variable['class']);
            if ($isObject) {
                $class = $variable['class'];
                if ($class == DateTime::class) {
                    $timestamp = $variable['timestamp'];
                    return [[$indentationAll . $timestamp, (new DateTime($timestamp))->formatShort(true)]];
                } elseif (is_subclass_of($class, Enum::class)) {
                    $value = $variable['value'];
                    $constants = call_user_func($class . '::getConstants');
                    return [[
                        $indentationAll . $value,
                        '\\' . $class . '::' . $constants[$value]
                    ]];
                }
                $linesAndComments = [[$indentationAll . '{', '\\' . $variable['class']]];
            } else {
                $isAssociative = ($variable && array_keys($variable) !== range(0, count($variable) - 1));
                $s = $indentationAll . ($isAssociative ? '{' : '[');
                if (!$variable) {
                    return [[$s . ']', null]];
                }
                $linesAndComments = [[$s, null]];
            }
            $isFirst = true;
            foreach ($variable as $name => $value) {
                if ('class' === $name) {
                    continue;
                }
                if ($isFirst) {
                    $isFirst = false;
                } else {
                    $linesAndComments[count($linesAndComments) - 1][0] .= ',';
                }
                $propertyLinesAndComments = $this
                    ->getReadableJsonLinesAndComments($value, $level + 1);
                $propertyLinesAndComments[0][0] = trim($propertyLinesAndComments[0][0]);
                if ($isObject || $isAssociative) {
                    $propertyLinesAndComments[0][0] = $name . ': ' . $propertyLinesAndComments[0][0];
                }
                $propertyLinesAndComments[0][0] = $indentationAll .
                    $indentationOne . $propertyLinesAndComments[0][0];
                $linesAndComments = array_merge(
                    $linesAndComments,
                    $propertyLinesAndComments
                );
            }
            $linesAndComments[] = [
                $indentationAll . ($isObject || $isAssociative ? '}' : ']'),
                null
            ];
            return $linesAndComments;
        } else {
            if (is_int($variable)) {
                $type = 'int';
            } elseif (is_float($variable)) {
                $type = 'float';
            } elseif (is_string($variable)) {
                $type = 'string';
            } else {
                $type = null;
            }
            return [[$indentationAll . json_encode($variable), $type]];
        }
    }

    /**
     * @param $variable
     * @param boolean $isClassNameRequired
     * @return mixed
     */
    private function doPickle($variable, $isClassNameRequired) {
        static $recursion = [];
        if (is_object($variable)) {
            $objectHash = spl_object_hash($variable);
            if (isset($recursion[$objectHash])) {
                return get_class($variable);
            }
            $recursion[$objectHash] = true;
            if ($variable instanceof Enum && !$isClassNameRequired) {
                $result = $variable->getValue();
            } elseif ($variable instanceof DateTime && !$isClassNameRequired) {
                $result = $variable->getTimestamp();
            } else {
                if ($isClassNameRequired) {
                    $result['class'] = get_class($variable);
                } else {
                    $result = [];
                }
                if ($variable instanceof DateTime) {
                    $result['timestamp'] = $variable->getTimestamp();
                }
                $properties = ReflectionPropertiesReader::service()
                    ->getProperties($variable);
                foreach ($properties as $property) {
                    $pickledValue = $this->doPickle(
                        $property->getValue($variable),
                        $isClassNameRequired
                    );
                    $result[$property->getName()] = $pickledValue;
                }
            }
            unset($recursion[$objectHash]);
        } elseif (is_array($variable)) {
            $result = [];
            foreach ($variable as $key => $value) {
                $pickledValue = $this->doPickle(
                    $value,
                    $isClassNameRequired
                );
                $result[$key] = $pickledValue;
            }
        } else {
            $result = $variable;
        }
        return $result;
    }

    private function doUnpickle($pickle) {
        if (!is_array($pickle)) {
            return $pickle;
        }
        $isClass = isset($pickle['class']);
        if ($isClass) {
            $class = $pickle['class'];
            $isClass = class_exists($class);
        }
        if ($isClass) {
            if ($class == DateTime::class) {
                return new DateTime($pickle['timestamp']);
            }
            $instance = new $class;
            $properties = ReflectionPropertiesReader::service()
                ->getProperties($instance);
            foreach ($properties as $property) {
                $propertyName = $property->getName();
                if (isset($pickle[$propertyName])) {
                    $property->setValue($instance, $this->doUnpickle($pickle[$propertyName]));
                }
            }
            return $instance;
        }
        $result = [];
        foreach ($pickle as $key => $value) {
            $result[$key] = $this->doUnpickle($value);
        }
        return $result;
    }

}
