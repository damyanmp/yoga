<?php

namespace Yoga\Console\Command\ApiDocumentation;

class ApiGroup {

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var \Yoga\Console\Command\ApiDocumentation\ApiRoute[]
     */
    private $routes = [];

    /**
     * @return string
     */
    public function getNamespace() {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     * @return ApiGroup
     */
    public function setNamespace($namespace) {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return ApiRoute[]
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * @param ApiRoute $route
     * @return ApiGroup
     */
    public function addRoute(ApiRoute $route) {
        $this->routes[] = $route;
        return $this;
    }

}
