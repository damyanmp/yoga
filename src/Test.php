<?php

namespace Yoga;

abstract class Test extends \PHPUnit_Framework_TestCase {

    protected function assertApiResponse(Api $endpoint, $expectedResult, $isDocument = true) {
        $actualResult = $endpoint->handle();
        $this->assertSame(Api::convertResponseToJson($expectedResult), Api::convertResponseToJson($actualResult));
        if ($isDocument) {
            \Yoga\Test\Documentor::service()->saveEndpointResult($endpoint, $expectedResult);
        }
    }

    /**
     * Get access to private and protected methods by name
     * @param object $object
     * @param string $methodName
     * @return \ReflectionMethod
     */
    protected function getMethod($object, $methodName) {
        $reflectionClass = new \ReflectionClass($object);
        if (!$reflectionClass) {
            return null;
        }
        $method = $reflectionClass->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

}
