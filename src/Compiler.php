<?php

namespace Yoga;

abstract class Compiler extends \Yoga\Service {

    /**
     * @param mixed $input
     * @return string
     */
    abstract public function compile($input);

    /**
     * @return string
     */
    public function getOutputFileRelativePath() {
        $result = substr(static::class, strrpos(static::class, '\\') + 1);
        return 'public_http/var/js/' . $result . '.js';
    }

    /**
     * @throws \Exception
     * @return \Yoga\Compiler\Reader
     */
    public function getReader() {
        $parts = explode('\\', static::class);
        $n = count($parts);
        for ($i = $n - 1; $i; $i--) {
            $namespace = implode('\\', array_slice($parts, 0, $i));
            $readerClassName = $namespace . '\\Reader\\' . $parts[$i];
            if (class_exists($readerClassName)) {
                return $readerClassName::service();
            }
        }
        throw new \Exception('Cannot locate reader for `' . static::class . '`');
    }

    public function writeCompiledResult() {
        $outputFilePath = \Yoga\Application::service()->getRootDirectory() .
            $this->getOutputFileRelativePath();
        if (!file_exists(dirname($outputFilePath))) {
            return '';
        }
        $compiledResult = $this->getCompiledResult();
        file_put_contents($outputFilePath, $compiledResult);
        return $compiledResult;
    }

    public function getCompiledResult() {
        $reader = $this->getReader();
        $readerHash = $reader->getHash();
        if ($readerHash) {
            if ($readerHash == $this->getCachedHash()) {
                return $this->getCachedResult(); // nothing changed, so the old file is still good
            }
        }
        $compiledResult = $this->compile($reader->read());
        if ($readerHash) {
            $this->cacheHash($readerHash);
            $this->cacheResult($compiledResult);
        }
        return $compiledResult;
    }

    private function cacheHash($hash) {
        Cache::service()->set($this->getCacheKeyNameForHash(), $hash, 0);
    }

    private function getCachedHash() {
        return Cache::service()->get($this->getCacheKeyNameForHash());
    }

    private function cacheResult($compiledResult) {
        Cache::service()->set($this->getCacheKeyNameForResult(), $compiledResult, 0);
    }

    private function getCachedResult() {
        return Cache::service()->get($this->getCacheKeyNameForResult());
    }

    private function getCacheKeyNameForHash() {
        return $this->getCacheKeyNameForResult() . '#';
    }

    private function getCacheKeyNameForResult() {
        return Configuration::service()->getHttpHost() . '_' . static::class;
    }

}