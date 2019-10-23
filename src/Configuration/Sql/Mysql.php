<?php

namespace Yoga\Configuration\Sql;

class Mysql extends \Yoga\Configuration\Sql {

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $userName;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var string
     */
    private $port;

    /**
     * @param string $databaseName
     * @return Mysql
     */
    public function setDatabaseName($databaseName) {
        $this->databaseName = $databaseName;
        return $this;
    }

    /**
     * @return string
     */
    public function getDatabaseName() {
        return $this->databaseName;
    }

    /**
     * @param string $host
     * @return Mysql
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
     * @return Mysql
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
     * @param string $userName
     * @return Mysql
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

    /**
     * @param string $port
     * @return Mysql
     */
    public function setPort($port) {
        $this->port = $port;
        return $this;
    }

    /**
     * @return string
     */
    public function getPort() {
        return $this->port;
    }

}