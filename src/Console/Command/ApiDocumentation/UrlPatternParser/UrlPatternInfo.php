<?php

namespace Yoga\Console\Command\ApiDocumentation\UrlPatternParser;

class UrlPatternInfo {

    /**
     * urlPattern with parameter constraints removed, so
     * '/blah/{id:\d+}' becomes '/blah/{id}'
     * @var string
     */
    private $urlPatternClarified;

    /**
     * For '/blah/{id:\d+}' $parameterConstraints will be:
     * ['id' => '\d+']
     * @var string[]
     */
    private $parameterConstraints = [];

    /**
     * @return \string[]
     */
    public function getParameterConstraints() {
        return $this->parameterConstraints;
    }

    /**
     * @param \string[] $parameterConstraints
     * @return UrlPatternInfo
     */
    public function setParameterConstraints($parameterConstraints) {
        $this->parameterConstraints = $parameterConstraints;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrlPatternClarified() {
        return $this->urlPatternClarified;
    }

    /**
     * @param string $urlPatternClarified
     * @return UrlPatternInfo
     */
    public function setUrlPatternClarified($urlPatternClarified) {
        $this->urlPatternClarified = $urlPatternClarified;
        return $this;
    }

}