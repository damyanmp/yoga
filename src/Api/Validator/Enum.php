<?php

namespace Yoga\Api\Validator;

class Enum extends \Yoga\Api\Validator {

    private $enumClass;

    public function __construct($enumClass) {
        $this->enumClass = $enumClass;
    }

    public function handle($rawValue) {
        $rawValue = parent::handle($rawValue);
        if (!$rawValue) {
            return null;
        }
        try {
            return new $this->enumClass($rawValue);
        } catch (\Exception $e) {
            throw new \Yoga\Api\Exception(
                'Invalid value supplied for `' .
                $this->getParameterName() . '`: `' . $rawValue . '`'
            );
        }
    }

}