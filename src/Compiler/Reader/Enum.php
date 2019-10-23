<?php

namespace Yoga\Compiler\Reader;

/**
 * @method static Enum service()
 */
class Enum extends \Yoga\Compiler\Reader {

    public function read() {
        return \Yoga\Configuration::service()->getJavascriptEnumClasses();
    }

}