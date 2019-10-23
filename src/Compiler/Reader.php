<?php

namespace Yoga\Compiler;

/**
 * @method static Reader service()
 */
abstract class Reader extends \Yoga\Service {

    /**
     * @return mixed
     */
    abstract public function read();

    /**
     * @return string
     */
    public function getHash() {
        return null;
    }

}