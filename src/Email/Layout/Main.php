<?php

namespace Yoga\Email\Layout;

class Main extends \Yoga\Template\Layout {

    /**
     * @var string
     */
    private $signatureName = 'Slyce team';

    /**
     * @return string
     */
    public function getSignatureName() {
        return $this->signatureName;
    }

    /**
     * @param string $signatureName
     * @return Main
     */
    public function setSignatureName($signatureName) {
        $this->signatureName = $signatureName;
        return $this;
    }

}