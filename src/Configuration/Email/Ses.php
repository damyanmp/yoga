<?php

namespace Yoga\Configuration\Email;

class Ses extends \Yoga\Configuration\Email {

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $security;

    /**
     * @var string
     */
    private $userName;

    /**
     * @var string
     */
    private $password;

    /**
     * @param string $host
     * @return Ses
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
     * @param string $password
     * @return Ses
     */
    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @param int $port
     * @return Ses
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
     * @param string $security
     * @return Ses
     */
    public function setSecurity($security) {
        $this->security = $security;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecurity() {
        return $this->security;
    }

    /**
     * @param string $userName
     * @return Ses
     */
    public function setUserName($userName) {
        $this->userName = $userName;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserName() {
        return $this->userName;
    }

}