<?php

namespace Yoga\Enum;

/**
 * @method static ResponseFileType wrap()
 */
class ResponseFileType extends \Yoga\Enum {

    const CSV = 1;

    public function getHttpResponseType() {
        switch ($this->getValue()) {
            case self::CSV:
                return 'application/csv';
        }
        throw new \Exception('Unknown response file type: `' . $this->getValue() . '`');
    }

}