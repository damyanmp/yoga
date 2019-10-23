<?php

namespace Yoga;

/**
 * Replacement for standard \SplEnum that doesn't require PECL
 */
abstract class Enum {

    /**
     * @var int
     */
    private $value = null;

    /**
     * @param int|Enum $value
     * @throws \Exception
     */
    public function __construct($value = null) {
        if (null === $value) {
            return;
        }
        if ($value instanceof self) {
            $value = $value->getValue();
        }
        if (!static::isValid($value)) {
            throw new \Exception('Invalid value: `' . var_export($value, true) . '`');
        }
        if ((int)$value == $value) {
            $value = (int)$value;
        }
        $this->value = $value;
    }

    /**
     * @param Enum|int $value
     * @return Enum
     */
    static public function wrap($value) {
        if (null === $value || !$value && !static::isValid($value)) {
            return null;
        }
        if ($value instanceof self) {
            return $value;
        }
        return new static($value);
    }

    /**
     * @param string $name
     * @throws \Exception
     * @return Enum
     */
    static public function createFromName($name) {
        $constants = static::getConstants();
        $value = array_search($name, $constants);
        if (false !== $value) {
            return new static($value);
        }
        throw new \Exception('Unknown name: `' . $name . '`');
    }

    /**
     * @return int
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @return int[]
     */
    static public function getConstants() {
        static $cache = [];
        $class = get_called_class();
        if (!array_key_exists($class, $cache)) {
            $reflectionClass = new \ReflectionClass($class);
            $constants = [];
            foreach ($reflectionClass->getConstants() as $name => $value) {
                $constants[$value] = $name;
            }
            $cache[$class] = $constants;
        }
        return $cache[$class];
    }

    /**
     * @param int $value
     * @return boolean
     */
    static public function isValid($value) {
        if (null === $value) {
            return true;
        }
        return false !== array_search($value, array_keys(static::getConstants()));
    }

    /**
     * @return string
     */
    final public function __toString() {
        return (string)$this->value;
    }

}
