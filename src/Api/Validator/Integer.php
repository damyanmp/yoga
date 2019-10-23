<?php

namespace Yoga\Api\Validator;

class Integer extends \Yoga\Api\Validator {

    /**
     * @var int
     */
    public $min;

    /**
     * @var int
     */
    public $max;

    public function handle($rawValue) {
        $rawValue = parent::handle($rawValue);
        if (!$rawValue) {
            return null;
        }
        $s = trim($rawValue);
        $result = intval($s);
        if (strval($result) !== $s) {
            throw new \Yoga\Api\Exception(
                'Invalid integer value supplied for `' .
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