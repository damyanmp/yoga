<?php

namespace Yoga;

/**
 * @method static ComputeOnce service()
 */
class ComputeOnce extends Service {

    public function handle(callable $compute) {
        if ($compute instanceof \Closure) {
            $backtrace = debug_backtrace();
            if ('::' == $backtrace[1]['type']) {
                throw new \Exception('ComputeOnce can only be used from a non-static method, or from outside a class - cannot use it from a static method (PHP limitation)');
            }
            if ('->' == $backtrace[1]['type']) {
                $key = get_class($backtrace[1]['object']);
            } else {
                $key = $backtrace[0]['file'];
            }
            $key .= $backtrace[0]['line'];
        } else {
            $key = (string)$compute;
        }
        static $cache = [];
        if (!array_key_exists($key, $cache)) {
            $cache[$key] = $compute();
        }
        return $cache[$key];
    }

}