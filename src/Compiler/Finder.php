<?php

namespace Yoga\Compiler;

/**
 * @method static Finder service()
 */
class Finder extends \Yoga\Service {

    /**
     * @return \Yoga\Compiler[]
     */
    public function findCompilers() {
        return [
            \Yoga\Compiler\Constant::service(),
            \Yoga\Compiler\Enum::service(),
            \Yoga\Compiler\Api\Jquery::service(),
            \Yoga\Compiler\Api\Angular::service(),
        ];
    }

}