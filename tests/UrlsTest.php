<?php

class UrlsTest extends \Yoga\Test {

    public function testAppendParameters() {
        $urlsService = \Yoga\Urls::service();
        $this->assertEquals(
            '/?name=value',
            $urlsService->appendParameters('/', ['name' => 'value'])
        );
        $this->assertEquals(
            '/?name=value&another=value2',
            $urlsService->appendParameters('/?name=value', ['another' => 'value2'])
        );
    }

}