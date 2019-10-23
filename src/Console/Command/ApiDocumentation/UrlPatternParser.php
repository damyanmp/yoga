<?php

namespace Yoga\Console\Command\ApiDocumentation;

/**
 * @method static UrlPatternParser service()
 */
class UrlPatternParser extends \Yoga\Service {

    /**
     * @param $urlPattern string
     * @return \Yoga\Console\Command\ApiDocumentation\UrlPatternParser\UrlPatternInfo
     */
    public function parse($urlPattern) {
        $urlPatternClarified = preg_replace('/(\{\w[^:]+)\:[^}]+\}/', '$1}', $urlPattern);
        preg_match_all('/\{(\w[^{:]+)\:([^}]+)\}/', $urlPattern, $matches);
        $n = count($matches[1]);
        $parameterConstraints = [];
        for ($i = 0; $i < $n; $i++) {
            $parameterConstraints[$matches[1][$i]] = $matches[2][$i];
        }
        return (new \Yoga\Console\Command\ApiDocumentation\UrlPatternParser\UrlPatternInfo)
            ->setUrlPatternClarified($urlPatternClarified)
            ->setParameterConstraints($parameterConstraints);
    }

}
