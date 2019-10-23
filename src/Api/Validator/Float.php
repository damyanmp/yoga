<?php

namespace Yoga\Api\Validator;

class Float extends \Yoga\Api\Validator {

    /**
     * @var float
     */
    public $min;

    /**
     * @var float
     */
    public $max;

    public function handle($rawValue) {
        $rawValue = parent::handle($rawValue);
        if (!$rawValue) {
            return null;
        }
        $s = trim($rawValue);
        $result = floatval($s);
        if (strval($result) !== $s) {
            throw new \Yoga\Api\Exception(
                'Invalid float value supplied for `' .
                $this->getParameterName() . '`: `' . $rawValue . '`'
            );
        }
        if ($this->min && $result < $this->min) {
            throw new \Yoga\Api\Exception(
                'The value for `' . $this->getParameterName() . '` should be' .
                ' no less than ' . $this->min . ' (got: `' . $rawValue . '`)'
            );
        }
        if ($this->max && $result > $this->max) {
            throw new \Yoga\Api\Exception(
                'The value for `' . $this->getParameterName() . '` should be' .
                ' no greater than ' . $this->max . ' (got: `' . $rawValue . '`)'
            );
        }
        return $result;
    }

}