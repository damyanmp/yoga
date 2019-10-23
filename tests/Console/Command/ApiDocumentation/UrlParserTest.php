<?php

namespace Yoga\Console\Command\ApiDocumentation;

class UrlParserTest extends \Yoga\Test {

    public function testParse() {
        $parser = UrlPatternParser::service();
        $url = '/blah/{id:\d+}/blah/{one-more}/blah/{another-one:\d\d\d\d-\d\d-\d\d}';
        $result = $parser->parse($url);
        $this->assertInstanceOf(
            \Yoga\Console\Command\ApiDocumentation\UrlPatternParser\UrlPatternInfo::class,
            $result
        );
        $this->assertSame(
            '/blah/{id}/blah/{one-more}/blah/{another-one}',
            $result->getUrlPatternClarified()
        );
        $this->assertSame(
            [
                'id' => '\d+',
                'another-one' => '\d\d\d\d-\d\d-\d\d'
            ],
            $result->getParameterConstraints()
        );
    }

}
