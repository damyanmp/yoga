<?php

namespace Yoga;

/**
 * @method static Urls service()
 */
class Urls extends \Yoga\Service {

    /**
     * @param string $url
     * @param string[] $parameters
     * @return string
     */
    public function appendParameters($url, array $parameters) {
        if (!$parameters) {
            return $url;
        }
        $result = $url;
        $isFirst = true;
        foreach ($parameters as $name => $value) {
            $result = $this->removeParameter($result, $name);
            if ($value) {
                if ($isFirst && false === strpos($result, '?')) {
                    $delimiter = '?';
                    $isFirst = false;
                } else {
                    $delimiter = '&';
                }
                $result .= $delimiter . $name . '=' . urlencode($value);
            }
        }
        return $result;
    }

    public function removeParameter($url, $parameterName) {
        $start = strpos($url, '&' . $parameterName);
        if (false === $start) {
            $start = strpos($url, '?' . $parameterName);
        }
        if (false === $start) {
            return $url;
        }
        $finish = 0;
        $l = strlen($url);
        for ($i = $start + strlen($parameterName) + 1; $i < $l; $i++) {
            if ($url[$i] === '&') {
                $finish = $i;
                break;
            }
        }
        if (!$finish) {
            return substr($url, 0, $start);
        }
        $d = ($url[$start] === '?' ? 1 : 0);
        return substr($url, 0, $start + $d) . substr($url, $finish + $d);
    }

    const BASE64_URL_UNFRIENDLY_CHARACTERS = '+/=';
    const BASE64_URL_FRIENDLY_REPLACEMENTS = '-_.';

    public function base64urlEncode($string) {
        return strtr(
            base64_encode($string),
            self::BASE64_URL_UNFRIENDLY_CHARACTERS,
            self::BASE64_URL_FRIENDLY_REPLACEMENTS
        );
    }

    public function base64urlDecode($string) {
        return base64_decode(
            strtr(
                $string,
                self::BASE64_URL_FRIENDLY_REPLACEMENTS,
                self::BASE64_URL_UNFRIENDLY_CHARACTERS
            )
        );
    }

    public function getRootUrl($absoluteUrl) {
        return parse_url($absoluteUrl, PHP_URL_SCHEME) . '://' .
            parse_url($absoluteUrl, PHP_URL_HOST);
    }

    public function getRelativeUrl($absoluteUrl) {
        $result = parse_url($absoluteUrl, PHP_URL_PATH);
        if (!$result) {
            $result = '/';
        }
        if (parse_url($absoluteUrl, PHP_URL_QUERY)) {
            $result .= '?' . parse_url($absoluteUrl, PHP_URL_QUERY);
        }
        return $result;
    }

}