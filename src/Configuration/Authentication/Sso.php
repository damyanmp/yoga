<?php

namespace Yoga\Configuration\Authentication;

class Sso {

    /**
     * @var string
     */
    private $ssoServerUrl;

    /**
     * @var boolean
     */
    private $isSslVerify;

    /**
     * If left empty, SSO server hosted login form is used
     * @var string
     */
    private $customLoginFormUrl;

    /**
     * @param string $url
     * @return Sso
     */
    public function setSsoServerUrl($url) {
        $this->ssoServerUrl = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getSsoServerUrl() {
        return $this->ssoServerUrl;
    }

    /**
     * @param string $customLoginFormUrl
     * @return Sso
     */
    public function setCustomLoginFormUrl($customLoginFormUrl) {
        $this->customLoginFormUrl = $customLoginFormUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomLoginFormUrl() {
        return $this->customLoginFormUrl;
    }

    /**
     * @param boolean $isSslVerify
     * @return Sso
     */
    public function setIsSslVerify($isSslVerify) {
        $this->isSslVerify = $isSslVerify;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsSslVerify() {
        return $this->isSslVerify;
    }

}