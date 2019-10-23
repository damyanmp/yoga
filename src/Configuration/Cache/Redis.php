<?php

namespace Yoga\Configuration\Cache;

class Redis extends \Yoga\Configuration\Cache {

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var int
     */
    private $db;

    /**
     * @param string $host
     * @return Redis
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
     * @return Redis
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

    /**
     * @param int $db
     * @return Redis
     */
    public function setDb($db) {
        $this->db = $db;
        return $this;
    }

    /**
     * @return int
     */
    public function getDb() {
        return $this->db;
    }

}