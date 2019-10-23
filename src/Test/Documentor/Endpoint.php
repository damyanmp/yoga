<?php

namespace Yoga\Test\Documentor;

class Endpoint {

    /**
     * @var string
     */
    private $endpointClass;

    /**
     * @var mixed
     */
    private $expectedResult;

    /**
     * @var string
     */
    private $testFilePath;

    /**
     * @var int
     */
    private $testFileLineNumber;

    /**
     * @var string
     */
    private $testMethod;

    /**
     * @return string
     */
    public function getEndpointClass() {
        return $this->endpointClass;
    }

    /**
     * @param string $endpointClass
     * @return Endpoint
     */
    public function setEndpointClass($endpointClass) {
        $this->endpointClass = $endpointClass;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpectedResult() {
        return $this->expectedResult;
    }

    /**
     * @param mixed $expectedResult
     * @return Endpoint
     */
    public function setExpectedResult($expectedResult) {
        $this->expectedResult = $expectedResult;
        return $this;
    }

    /**
     * @return string
     */
    public function getTestFilePath() {
        return $this->testFilePath;
    }

    /**
     * @param string $testFilePath
     * @return Endpoint
     */
    public function setTestFilePath($testFilePath) {
        $this->testFilePath = $testFilePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getTestMethod() {
        return $this->testMethod;
    }

    /**
     * @param string $testMethod
     * @return Endpoint
     */
    public function setTestMethod($testMethod) {
        $this->testMethod = $testMethod;
        return $this;
    }

    /**
     * @return int
     */
    public function getTestFileLineNumber() {
        return $this->testFileLineNumber;
    }

    /**
     * @param int $testFileLineNumber
     * @return Endpoint
     */
    public function setTestFileLineNumber($testFileLineNumber) {
        $this->testFileLineNumber = $testFileLineNumber;
        return $this;
    }

}