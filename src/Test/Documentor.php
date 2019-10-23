<?php

namespace Yoga\Test;

/**
 * @method static Documentor service()
 */
class Documentor extends \Yoga\Service {

    /**
     * @var \Yoga\Test\Documentor\Endpoint[]
     */
    private $savedEndpointResults = [];

    static public function getEndpointResultsPickleFilePath() {
        return \Yoga\Application::service()->getRootDirectory() . 'var/endpoint-results.pickle';
    }

    public function saveEndpointResult(\Yoga\Api $endpoint, $expectedResult) {
        $backtrace = debug_backtrace();
        $testFilePath = \Yoga\Formatter::service()->formatPath(
            substr(
                $backtrace[1]['file'],
                strlen(\Yoga\Application::service()->getRootDirectory())
            )
        );
        $this->savedEndpointResults[] = (new \Yoga\Test\Documentor\Endpoint)
            ->setEndpointClass(get_class($endpoint))
            ->setExpectedResult($expectedResult)
            ->setTestFilePath($testFilePath)
            ->setTestFileLineNumber($backtrace[1]['line'])
            ->setTestMethod($backtrace[1]['object']->getName());
        $this->ensureSaveOnShutdown();
    }

    /**
     * @param string $endpointClass
     * @return \Yoga\Test\Documentor\Endpoint[]
     */
    public function restoreEndpointResults($endpointClass) {
        $allEndpointResults = $this->restoreAllEndpointResults();
        $result = [];
        if (is_array($allEndpointResults)) {
            foreach ($allEndpointResults as $endpointResult) {
                if ($endpointResult->getEndpointClass() == $endpointClass) {
                    $result[] = $endpointResult;
                }
            }
        }
        return $result;
    }

    /**
     * @return \Yoga\Test\Documentor\Endpoint[]
     */
    private function restoreAllEndpointResults() {
        return \Yoga\ComputeOnce::service()->handle(function () {
            $filePath = self::getEndpointResultsPickleFilePath();
            if (!file_exists($filePath)) {
                return null;
            }
            return \Yoga\Pickler::service()
                ->unpickle(
                    unserialize(
                        file_get_contents($filePath)
                    )
                );
        });
    }

    private function ensureSaveOnShutdown() {
        \Yoga\ComputeOnce::service()->handle(function () {
            register_shutdown_function(function () {
                file_put_contents(
                    $this->getEndpointResultsPickleFilePath(),
                    serialize(
                        \Yoga\Pickler::service()
                            ->pickle($this->savedEndpointResults)
                    )
                );
            });
        });
    }

}