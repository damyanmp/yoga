<?php

namespace Yoga\Response;

class FileInMemory {

    /**
     * @var \Yoga\Enum\ResponseFileType
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $content;

    /**
     * @param \Yoga\Enum\ResponseFileType $type
     * @return FileInMemory
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * @return \Yoga\Enum\ResponseFileType
     */
    public function getType() {
        return new \Yoga\Enum\ResponseFileType($this->type);
    }

    /**
     * @param string $content
     * @return FileInMemory
     */
    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @param string $name
     * @return FileInMemory
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

}