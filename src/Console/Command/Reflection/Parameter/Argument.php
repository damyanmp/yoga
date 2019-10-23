<?php

namespace Yoga\Console\Command\Reflection\Parameter;

class Argument extends \Yoga\Console\Command\Reflection\Parameter {

    /**
     * @var boolean
     */
    private $isOption;

    /**
     * @var boolean
     */
    private $isRequired;

    /**
     * @var boolean
     */
    private $isArray;

    /**
     * @param boolean $isOption
     * @return Argument
     */
    public function setIsOption($isOption) {
        $this->isOption = $isOption;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsOption() {
        return $this->isOption;
    }

    /**
     * @param boolean $isArray
     * @return Argument
     */
    public function setIsArray($isArray) {
        $this->isArray = $isArray;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsArray() {
        return $this->isArray;
    }

    /**
     * @param boolean $isRequired
     * @return Argument
     */
    public function setIsRequired($isRequired) {
        $this->isRequired = $isRequired;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsRequired() {
        return $this->isRequired;
    }

}