<?php

namespace Yoga\Reflection;

class Annotation {

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @param array $arguments
     * @return Annotation
     */
    public function setArguments($arguments) {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @return array
     */
    public function getArguments() {
        return $this->arguments;
    }

    /**
     * @param string $name
     * @return Annotation
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

}