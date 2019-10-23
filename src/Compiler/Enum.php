<?php

namespace Yoga\Compiler;

class Enum extends \Yoga\Compiler {

    /**
     * @param array $input
     * @return string
     */
    public function compile($input) {
        $s = '';
        foreach ($input as $enumClass) {
            if ($s) {
                $s .= ',';
            }
            $s .= str_replace('\\', '_', $enumClass) . ': {';
            $ss = '';
            foreach ($enumClass::getConstants() as $value => $name) {
                if ($ss) {
                    $ss .= ',';
                }
                $ss .= $name . ': ' . $value;
            }
            $s .= $ss . '}';
        }
        return 'var Enum = {' . $s . '};';
    }

}