<?php

namespace Yoga;

abstract class Migration extends \Yoga\Service {

    /**
     * @var boolean
     */
    private $isTestDatabase;

    abstract public function up();

    /**
     * @return boolean
     */
    public function getIsTestDatabase() {
        return $this->isTestDatabase;
    }

    /**
     * @param boolean $isTestDatabase
     * @return Migration
     */
    public function setIsTestDatabase($isTestDatabase) {
        $this->isTestDatabase = $isTestDatabase;
        return $this;
    }

}