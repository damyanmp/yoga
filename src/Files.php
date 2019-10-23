<?php

namespace Yoga;

/**
 * @method static Files service()
 */
class Files extends Service {

    public function chmod($pathname, $filemode, $isRecursive = false) {
        if (!$isRecursive) {
            chmod($pathname, $filemode);
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($pathname)
        );
        foreach ($iterator as $item) {
            chmod($item, $filemode);
        }
    }

}