<?php

/**
 * @method static Linux service()
 */
class Linux extends \Yoga\Service {

    public function getFreeMemoryBytes() {
        $file = fopen('/proc/meminfo', 'r');
        $result = false;
        $a = [];
        while ($line = fgets($file)) {
            if (preg_match('/^MemFree:\s+(\d+)\skB$/', $line, $a)) {
                $result = $a[1];
                break;
            }
        }
        fclose($file);
        return $result;
    }

}