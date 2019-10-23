<?php

namespace Yoga;

class Reflection {

    /**
     * @var \Yoga\Reflection\Annotation[]
     */
    private $annotations;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var \Yoga\Reflection\Property[]
     */
    private $properties;

    /**
     * @param \Yoga\Reflection\Property[] $properties
     * @return Reflection
     */
    public function setProperties(array $properties) {
        $this->properties = $properties;
        return $this;
    }

    /**
     * @return \Yoga\Reflection\Property[]
     */
    public function getProperties() {
        return $this->properties;
    }

    /**
     * @param \Yoga\Reflection\Annotation[] $annotations
     * @return Reflection
     */
    public function setAnnotations($annotations) {
        $this->annotations = $annotations;
        return $this;
    }

    /**
     * @return \Yoga\Reflection\Annotation[]
     */
    public function getAnnotations() {
        return $this->annotations;
    }

    /**
     * @return string
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return Reflection
     */
    public function setComment($comment) {
        $this->comment = $comment;
        return $this;
    }

}