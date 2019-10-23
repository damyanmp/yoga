<?php

namespace Yoga;

class Service {

    static private $service = [];

    static public function service() {
        if (!isset(self::$service[static::class])) {
            self::$service[static::class] = new static;
        } elseif (is_string(self::$service[static::class])) {
            self::$service[static::class] = call_user_func([self::$service[static::class], 'service']);
        }
        return self::$service[static::class];
    }

    static public function substitute($substituteClass) {
        self::$service[static::class] = $substituteClass;
    }

}
