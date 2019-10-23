<?php

namespace Yoga;

/**
 * @method static Arrays service()
 */
class Arrays extends Service {

    public function safeGet($array, $key) {
        if (!is_array($array)) {
            return null;
        }
        if (!isset($array[$key])) {
            return null;
        }
        return $array[$key];
    }

}