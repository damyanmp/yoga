<?php

namespace Yoga\Compiler\Reader;

/**
 * @method static Constant service()
 */
class Constant extends \Yoga\Compiler\Reader {

    public function read() {
        return \Yoga\Configuration::service()->getJavascriptConstants();
    }

}