<?php

namespace Yoga\Configuration;

abstract class Email {

    /**
     * @var string
     */
    private $supportFromAddress;

    /**
     * @var \Yoga\Template\Layout
     */
    private $layout;

    /**
     * @param \Yoga\Template\Layout $layout
     * @return Email
     */
    public function setLayout(\Yoga\Template\Layout $layout = null) {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @return \Yoga\Template\Layout
     */
    public function getLayout() {
        return $this->layout;
    }

    /**
     * @param string $supportFromAddress
     * @return Email
     */
    public function setSupportFromAddress($supportFromAddress) {
        $this->supportFromAddress = $supportFromAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getSupportFromAddress() {
        return $this->supportFromAddress;
    }

}