<?php

namespace Yoga\Session;

class RedirectContext {

    /**
     * @var string
     */
    private $url;

    /**
     * @var \Yoga\Enum\HttpRequestMethod
     */
    private $httpRequestMethod;

    /**
     * @var array
     */
    private $postParameters;

    static public function createFromGlobals() {
        return (new self)
            ->setHttpRequestMethod(
                \Yoga\Enum\HttpRequestMethod
                    ::createFromName($_SERVER['REQUEST_METHOD'])
            )
            ->setUrl($_SERVER['REQUEST_URI'])
            ->setPostParameters($_POST);
    }

    public function redirect() {
        if (\Yoga\Enum\HttpRequestMethod::POST != $this->getHttpRequestMethod()->getValue()) {
            header('Location: ' . $this->getUrl());
            return;
        }
        $htmlFields = '';
        foreach ($this->getPostParameters() as $name => $value) {
            $htmlFields .= '<input type="hidden" name=' . json_encode($name) .
                ' value=' . json_encode($value) . '>';
        }
        $html = '<html>
            <body onload="document.getElementById(\'f\').submit()">
            <form id="f" action="' . $this->getUrl() . '" method="POST">
                ' . $htmlFields . '
            </form>
            </body>
            </html>
        ';
        echo $html;
    }

    /**
     * @param \Yoga\Enum\HttpRequestMethod|int $httpRequestMethod
     * @return RedirectContext
     */
    public function setHttpRequestMethod($httpRequestMethod) {
        $this->httpRequestMethod = $httpRequestMethod;
        return $this;
    }

    /**
     * @return \Yoga\Enum\HttpRequestMethod
     */
    public function getHttpRequestMethod() {
        return \Yoga\Enum\HttpRequestMethod::wrap($this->httpRequestMethod);
    }

    /**
     * @param array $postParameters
     * @return RedirectContext
     */
    public function setPostParameters($postParameters) {
        $this->postParameters = $postParameters;
        return $this;
    }

    /**
     * @return array
     */
    public function getPostParameters() {
        return $this->postParameters;
    }

    /**
     * @param string $url
     * @return RedirectContext
     */
    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

}