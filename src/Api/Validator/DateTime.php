<?php

namespace Yoga\Api\Validator;

class DateTime extends Integer {

    public function handle($rawValue) {
        $result = parent::handle($rawValue);
        if (!$result) {
            return null;
        }
        return new \Yoga\DateTime($result);
    }

}