<?php

namespace Yoga\Template;

class Email extends \Yoga\Template {

    /**
     * @var string
     */
    private $from;

    /**
     * @var string|string[]
     */
    private $to;

    /**
     * @var string[]
     */
    private $cc = [];

    /**
     * @var string[]
     */
    private $bcc = [];

    public function send() {
        if (!$this->getTo() && !$this->getCc() && !$this->getBcc()) {
            return;
        }
        if (!$this->getFrom()) {
            $this->setFrom(
                \Yoga\Configuration::service()
                    ->getEmailConfiguration()
                    ->getSupportFromAddress()
            );
        }
        if (!$this->getLayout()) {
            $this->setLayout(
                \Yoga\Configuration::service()
                    ->getEmailConfiguration()
                    ->getLayout()
            );
        }
        \Yoga\Email::service()->mail(
            $this->getFrom(),
            $this->getTo(),
            $this->formatSubject(),
            $this->formatBodyHtml(),
            $this->getCc(),
            $this->getBcc()
        );
    }

    private function formatSubject() {
        return $this->formatPartial('subject', true);
    }

    private function formatBodyHtml() {
        return $this->formatPartial('body', false);
    }

    private function formatPartial($partialName, $isNullLayout) {
        $this->overridePhtmlFileName = str_replace(
            '.phtml',
            '.' . $partialName . '.phtml',
            parent::getPhtmlFileName()
        );
        $savedLayout = $this->getLayout();
        if ($isNullLayout) {
            $this->setLayout(null);
        }
        $result = $this->render();
        if ($isNullLayout) {
            $this->setLayout($savedLayout);
        }
        return $result;
    }

    /**
     * @var string
     */
    private $overridePhtmlFileName;

    protected function getPhtmlFileName() {
        return $this->overridePhtmlFileName;
    }

    /**
     * @param \string[] $bcc
     * @return Email
     */
    public function setBcc(array $bcc = []) {
        $this->bcc = $bcc;
        return $this;
    }

    /**
     * @return \string[]
     */
    public function getBcc() {
        return $this->bcc;
    }

    /**
     * @param \string[] $cc
     * @return Email
     */
    public function setCc(array $cc = []) {
        $this->cc = $cc;
        return $this;
    }

    /**
     * @return \string[]
     */
    public function getCc() {
        return $this->cc;
    }

    /**
     * @param string $from
     * @return Email
     */
    public function setFrom($from) {
        $this->from = $from;
        return $this;
    }

    /**
     * @return string
     */
    public function getFrom() {
        return $this->from;
    }

    /**
     * @param string $to
     * @return Email
     */
    public function setTo($to) {
        $this->to = $to;
        return $this;
    }

    /**
     * @return string
     */
    public function getTo() {
        return $this->to;
    }

}