<?php

namespace Yoga\Template;

class Layout extends \Yoga\Template {

    /**
     * @var string
     */
    private $content;

    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    public function getContent() {
        return $this->content;
    }

}