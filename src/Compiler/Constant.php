<?php

namespace Yoga\Compiler;

class Constant extends \Yoga\Compiler {

    /**
     * @param array $input
     * @return string
     */
    public function compile($input) {
        return 'var Constant = ' . json_encode($input) . ';';
    }

}