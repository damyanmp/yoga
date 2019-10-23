<?php

namespace Yoga\Configuration\Cache;

class Memcache extends \Yoga\Configuration\Cache {

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @param string $host
     * @return Memcache
     */
    public function setHost($host) {
        $this->host = $host;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * @param int $port
     * @return Memcache
     */
    public function setPort($port) {
        $this->port = $port;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort() {
        return $this->port;
    }

}