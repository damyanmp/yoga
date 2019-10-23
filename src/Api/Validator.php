<?php

namespace Yoga\Api;

class Validator {

    /**
     * @var string
     */
    private $parameterName;

    /**
     * @var boolean
     */
    public $isRequired;

    /**
     * Sanitize, parse, validate and convert $rawValue into the actual final
     * value injected as the Api parameter. Throw \Yoga\Api\Exception on
     * validation errors.
     * @param mixed $rawValue
     * @throws \Exception
     * @return mixed
     */
    public function handle($rawValue) {
        if ($this->isRequired && !$rawValue) {
            throw new \Yoga\Api\Exception(
                'A value is expected for `' . $this->getParameterName() . '`'
            );
        }
        return $rawValue;
    }

    /**
     * @param \Yoga\Enum\PropertyType $type
     * @param boolean $isArray
     * @param array $attributes
     * @return Validator
     * @throws \Exception
     */
    static public function factory(
        \Yoga\Enum\PropertyType $type = null,
        $isArray,
        array $attributes
    ) {
        if ($isArray) {
            if (!$type) {
                $type = \Yoga\Enum\PropertyType::wrap(\Yoga\Enum\PropertyType::OBJECT);
            }
            return (new \Yoga\Api\Validator\ArrayValidator)
                ->setItemValidator(self::factory($type, false, $attributes));
        }
        if (!$type) {
            $type = \Yoga\Enum\PropertyType::wrap(\Yoga\Enum\PropertyType::STRING);
        }
        $validator = self::createValidatorByType($type);
        foreach ($attributes as $name => $value) {
            // TODO: check if property actually exists on validator, throw exception otherwise
            $validator->$name = $value;
        }
        return $validator;
    }

    static private function createValidatorByType(\Yoga\Enum\PropertyType $type = null) {
        switch ($type->getValue()) {
            case \Yoga\Enum\PropertyType::INTEGER:
                return new \Yoga\Api\Validator\Integer;
            case \Yoga\Enum\PropertyType::STRING:
                return new \Yoga\Api\Validator\String;
            case \Yoga\Enum\PropertyType::FLOAT:
                return new \Yoga\Api\Validator\Float;
            case \Yoga\Enum\PropertyType::BOOLEAN:
                return new \Yoga\Api\Validator\Boolean;
            case \Yoga\Enum\PropertyType::ENUM:
                return new \Yoga\Api\Validator\Enum($type->getPropertyClass());
            case \Yoga\Enum\PropertyType::TIME:
                return new \Yoga\Api\Validator\DateTime;
            case \Yoga\Enum\PropertyType::OBJECT:
                return new \Yoga\Api\Validator;
        }
        throw new \Exception('No Validator registered for type `' . $type->getValue() . '`');
    }

    /**
     * @param mixed $parameterName
     * @return Validator
     */
    public function setParameterName($parameterName) {
        $this->parameterName = $parameterName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParameterName() {
        return $this->parameterName;
    }

}