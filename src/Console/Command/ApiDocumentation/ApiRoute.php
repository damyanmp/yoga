<?php

namespace Yoga\Console\Command\ApiDocumentation;

class ApiRoute {

    /**
     * @var string
     */
    private $urlPattern;

    /**
     * @var \Yoga\Api\Reflection[]
     */
    private $apis = [];

    /**
     * @return \Yoga\Api\Reflection[]
     */
    public function getApis() {
        return $this->apis;
    }

    /**
     * @param \Yoga\Api\Reflection $api
     * @return ApiRoute
     */
    public function addApi(\Yoga\Api\Reflection $api) {
        $this->apis[] = $api;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrlPattern() {
        return $this->urlPattern;
    }

    /**
     * @param string $urlPattern
     * @return ApiRoute
     */
    public function setUrlPattern($urlPattern) {
        $this->urlPattern = $urlPattern;
        return $this;
    }

}
