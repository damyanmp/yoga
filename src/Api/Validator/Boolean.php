<?php

namespace Yoga\Api\Validator;

class Boolean extends \Yoga\Api\Validator {

    public function handle($rawValue) {
        $rawValue = parent::handle($rawValue);
        if (!$rawValue) {
            return null;
        }
        return $rawValue !== 0 && strtolower($rawValue) !== 'false';
    }

}