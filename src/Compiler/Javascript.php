<?php

namespace Yoga\Compiler;

/**
 * @method static Javascript service()
 */
class Javascript extends \Yoga\Service {

    public function getCompiledResult($relativeUrl) {
        $compilers = Finder::service()->findCompilers();
        foreach ($compilers as $compiler) {
            if ($compiler->getOutputFileRelativePath() == 'public_http' . $relativeUrl) {
                return $compiler->writeCompiledResult();
            }
        }
    }

}