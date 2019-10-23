<?php

namespace Yoga\Api\Validator;

class String extends \Yoga\Api\Validator {

    /**
     * @var int
     */
    public $minLength;

    /**
     * @var int
     */
    public $maxLength;

    /**
     * @var boolean
     */
    public $isEmail;

    public function handle($rawValue) {
        $result = parent::handle($rawValue);
        if (!$result) {
            return null;
        }
        if (!is_string($result)) {
            throw new \Yoga\Api\Exception(
                'String expected for `' . $this->getParameterName() . '`)'
            );
        }
        $resultLength = strlen($result);
        if ($this->minLength && $resultLength < $this->minLength) {
            throw new \Yoga\Api\Exception(
                'The length for `' . $this->getParameterName() . '` should be' .
                ' no less than ' . $this->minLength . ' (got: `' . $resultLength . '`)'
            );
        }
        if ($this->maxLength && $resultLength > $this->maxLength) {
            throw new \Yoga\Api\Exception(
                'The length for `' . $this->getParameterName() . '` should be' .
                ' no greater than ' . $this->maxLength . ' (got: `' . $resultLength . '`)'
            );
        }
        if ($this->isEmail && !filter_var($result, FILTER_VALIDATE_EMAIL)) {
            throw new \Yoga\Api\Exception(
                'Email address expected for `' . $this->getParameterName() . '`'
            );
        }
        return $result;
    }

}