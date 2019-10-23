<?php

namespace Yoga\Console\Command;

class Reflection {

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \Yoga\Console\Command\Reflection\Parameter[]
     */
    private $parameters;

    /**
     * @param string $description
     * @return Reflection
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $name
     * @return Reflection
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

    /**
     * @param \Yoga\Console\Command\Reflection\Parameter[] $parameters
     * @return Reflection
     */
    public function setParameters(array $parameters) {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @return \Yoga\Console\Command\Reflection\Parameter[]
     */
    public function getParameters() {
        return $this->parameters;
    }

}