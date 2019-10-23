<?php

namespace Yoga\Api;

class Exception extends \Exception {

    /**
     * @var int
     */
    private $errorCode;

    /**
     * @param string $message
     * @param int|\Yoga\Enum\HttpResponseCode $httpCode
     * @param int $errorCode
     */
    public function __construct(
        $message,
        $httpCode = \Yoga\Enum\HttpResponseCode::BAD_REQUEST,
        $errorCode = null
    ) {
        $httpCode = new \Yoga\Enum\HttpResponseCode($httpCode);
        parent::__construct($message, $httpCode->getValue());
        $this->errorCode = $errorCode;
    }

    /**
     * @param int $errorCode
     * @return Exception
     */
    public function setErrorCode($errorCode) {
        $this->errorCode = $errorCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getErrorCode() {
        return $this->errorCode;
    }

}