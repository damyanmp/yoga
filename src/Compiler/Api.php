<?php

namespace Yoga\Compiler;

abstract class Api extends \Yoga\Compiler {

    /**
     * @return string
     */
    abstract protected function getOutputFileName();

    /**
     * @return string
     */
    abstract protected function compileHeader();

    /**
     * @param \Yoga\Api\Reflection $reflection
     * @return string
     */
    abstract protected function compileOneEndpoint(\Yoga\Api\Reflection $reflection);

    /**
     * @return string
     */
    abstract protected function compileSessionEndpoints();

    /**
     * @return string
     */
    abstract protected function compileFooter();

    /**
     * @return string
     */
    public function getOutputFileRelativePath() {
        return 'public_http/var/js/' . $this->getOutputFileName();
    }

    /**
     * @param \Yoga\Api\Reflection[] $input
     * @return string
     */
    public function compile($input) {
        $result = $this->compileHeader();
        foreach ($input as $reflection) {
            $result .= "\n\n" . $this->compileOneEndpoint($reflection);
        }
        $result .= $this->compileSessionEndpoints();
        $result .= $this->compileFooter();
        return $result;
    }

}