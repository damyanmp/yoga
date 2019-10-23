<?php

namespace Yoga\Enum;

/**
 * @method static PropertyType wrap()
 */
class PropertyType extends \Yoga\Enum {

    const INTEGER = 1;
    const STRING = 2;
    const FLOAT = 3;
    const BOOLEAN = 4;
    const ENUM = 5;
    const TIME = 6;
    const OBJECT = 7;

    /**
     * @var string
     */
    private $propertyClass;

    static public function createFromAnnotationTypeString($type) {
        $result = new self(self::getValueByAnnotationTypeString($type));
        if (!$result->getValue()) {
            $result = new self(is_subclass_of($type, \Yoga\Enum::class) ? self::ENUM : self::OBJECT);
            $result->setPropertyClass($type);
        }
        return $result;
    }

    static private function getValueByAnnotationTypeString($type) {
        if (substr($type, 0, 1) == '\\') {
            $type = substr($type, 1);
        }
        switch (strtolower($type)) {
            case 'int':
            case 'integer':
                return self::INTEGER;
            case 'string':
                return self::STRING;
            case 'float':
                return self::FLOAT;
            case \Yoga\DateTime::class:
                return self::TIME;
            case 'bool':
            case 'boolean':
                return self::BOOLEAN;
        }
        return null;
    }

    public function getName() {
        switch ($this->getValue()) {
            case self::INTEGER:
                return 'int';
            case self::STRING:
                return 'string';
            case self::FLOAT:
                return 'float';
            case self::BOOLEAN:
                return 'boolean';
            case self::ENUM:
                return $this->propertyClass;
            case self::TIME:
                return '\\' . \Yoga\DateTime::class;
            case self::OBJECT:
                return $this->propertyClass;
        }
    }

    public function getPropertyClass() {
        return $this->propertyClass;
    }

    /**
     * @param string $propertyClass
     * @return PropertyType
     */
    public function setPropertyClass($propertyClass) {
        $this->propertyClass = $propertyClass;
        return $this;
    }

}