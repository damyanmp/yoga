<?php

namespace Yoga;

class Template {

    /**
     * @var \Yoga\Template\Layout
     */
    private $layout;

    public function render() {
        ob_start();
        require $this->getPhtmlDirectory() . $this->getPhtmlFileName();
        $result = ob_get_clean();
        if ($this->layout) {
            $this->layout->setContent($result);
            $result = $this->layout->render();
        }
        return $result;
    }

    protected function getPhtmlDirectory() {
        $reflectionClass = new \ReflectionClass($this);
        $templateClassDirectory = dirname($reflectionClass->getFilename());
        return $templateClassDirectory . '/' . $this->getLanguagePrefix() . '/';
    }

    protected function getPhtmlFileName() {
        $fullClassName = get_called_class();
        if ($p = strrpos($fullClassName, '\\')) {
            $className = substr($fullClassName, $p + 1);
        } else {
            $className = $fullClassName;
        }
        return $className . '.phtml';
    }

    public function getLanguagePrefix() {
        return 'en';
    }

    /**
     * @param \Yoga\Template\Layout $layout
     * @return \Yoga\Template
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

}
