<?php

namespace Yoga\Compiler\Reader;

/**
 * @method static Api service()
 */
class Api extends \Yoga\Compiler\Reader {

    public function read() {
        return \Yoga\Api\Reflection\Reader::service()->getReflections();
    }

}