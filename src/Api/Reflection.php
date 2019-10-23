<?php

namespace Yoga\Api;

class Reflection {

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $urlPattern;

    /**
     * @var string[]
     */
    private $routeParameters;

    /**
     * @var boolean
     */
    private $isLoginRequired;

    /**
     * @var string
     */
    private $permissionRequired;

    /**
     * @var string|string[]
     */
    private $method;

    /**
     * @var \Yoga\Reflection\Property[]
     */
    private $parameters;

    /**
     * @var string
     */
    private $comment;

    /**
     * @param string|\string[] $method
     * @return Reflection
     */
    public function setMethod($method) {
        $this->method = $method;
        return $this;
    }

    /**
     * @return string|\string[]
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * @param string $urlPattern
     * @return Reflection
     */
    public function setUrlPattern($urlPattern) {
        $this->urlPattern = $urlPattern;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrlPattern() {
        return $this->urlPattern;
    }

    /**
     * @param \Yoga\Reflection\Property[] $parameters
     * @return Reflection
     */
    public function setParameters(array $parameters) {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @return \Yoga\Reflection\Property[]
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * @param string $class
     * @return Reflection
     */
    public function setClass($class) {
        $this->class = $class;
        return $this;
    }

    /**
     * @return string
     */
    public function getClass() {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getClassWithoutNamespace() {
        return substr($this->class, strrpos($this->class, '\\') + 1);
    }

    /**
     * @param \string[] $routeParameters
     * @return Reflection
     */
    public function setRouteParameters(array $routeParameters) {
        $this->routeParameters = $routeParameters;
        return $this;
    }

    /**
     * @return \string[]
     */
    public function getRouteParameters() {
        return $this->routeParameters;
    }

    /**
     * @param boolean $isLoginRequired
     * @return Reflection
     */
    public function setIsLoginRequired($isLoginRequired) {
        $this->isLoginRequired = $isLoginRequired;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsLoginRequired() {
        return $this->isLoginRequired;
    }

    /**
     * @return string
     */
    public function getPermissionRequired() {
        return $this->permissionRequired;
    }

    /**
     * @param string $permissionRequired
     * @return Reflection
     */
    public function setPermissionRequired($permissionRequired) {
        $this->permissionRequired = $permissionRequired;
        return $this;
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