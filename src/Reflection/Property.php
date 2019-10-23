<?php

namespace Yoga\Reflection;

class Property {

    /**
     * @var string
     */
    private $name;

    /**
     * @var \Yoga\Enum\PropertyType
     */
    private $type;

    /**
     * @var boolean
     */
    private $isArray;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var string
     */
    private $comment;

    /**
     * @param string $name
     * @return Property
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
     * @param array $attributes
     * @return Property
     */
    public function setAttributes(array $attributes) {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * @param \Yoga\Enum\PropertyType|int $type
     * @return Property
     */
    public function setType($type) {
        $this->type = \Yoga\Enum\PropertyType::wrap($type);
        return $this;
    }

    /**
     * @return \Yoga\Enum\PropertyType
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return Property
     */
    public function setComment($comment) {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsArray() {
        return $this->isArray;
    }

    /**
     * @param boolean $isArray
     * @return Property
     */
    public function setIsArray($isArray) {
        $this->isArray = $isArray;
        return $this;
    }

}