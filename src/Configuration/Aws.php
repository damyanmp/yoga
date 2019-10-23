<?php

namespace Yoga\Configuration;

class Aws {

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var string
     */
    private $region;

    /**
     * @param string $key
     * @return Aws
     */
    public function setKey($key) {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * @param string $secret
     * @return Aws
     */
    public function setSecret($secret) {
        $this->secret = $secret;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecret() {
        return $this->secret;
    }

    /**
     * @return string
     */
    public function getRegion() {
        return $this->region;
    }

    /**
     * @param string $region
     * @return Aws
     */
    public function setRegion($region) {
        $this->region = $region;
        return $this;
    }

}