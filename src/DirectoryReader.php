<?php

namespace Yoga;

/**
 * @method static DirectoryReader service()
 */
class DirectoryReader extends Service {

    /**
     * @param string $directory
     * @return \SplFileInfo[]
     */
    public function getFiles($directory) {
        $result = [];
        if (!file_exists($directory)) {
            return $result;
        }
        if (substr($directory, -1) != '/') {
            $directory .= '/';
        }
        /** @var $item \DirectoryIterator */
        foreach (new \DirectoryIterator($directory) as $item) {
            if (substr($item->getFilename(), 0, 1) == '.') {
                continue;
            }
            if ($item->isDir()) {
                $subdir = $item->getFilename();
                $result = array_merge($result, $this->getFiles($directory . $subdir));
            } else {
                $result[] = $item->getFileInfo();
            }
        }
        return $result;
    }

    /**
     * @param string $directory
     * @param string $namespace
     * @param boolean $isSkipAbstract
     * @return \ReflectionClass[]
     */
    public function getReflections($directory, $namespace, $isSkipAbstract = true) {
        $files = $this->getFiles($directory);
        $l = strlen($directory);
        $result = [];
        foreach ($files as $file) {
            $pathName = $file->getPathname();
            $class = $namespace . '\\' . substr(str_replace('/', '\\', $pathName), $l, -4);
            if (!class_exists($class)) {
                continue;
            }
            $reflection = new \ReflectionClass($class);
            if ($isSkipAbstract && $reflection->isAbstract()) {
                continue;
            }
            $result[] = $reflection;
        }
        return $result;
    }

}
