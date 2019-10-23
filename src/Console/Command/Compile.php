<?php

namespace Yoga\Console\Command;

/**
 * @Command("compile", description = "Compile static resources such as javascript files (/public_http/var/js/Api.js etc.)")
 */
class Compile extends \Yoga\Console\Command {

    public function handle() {
        $compilers = $this->getCompilers();
        $s = '';
        $i = 0;
        foreach ($compilers as $compiler) {
            $s .= PHP_EOL . (++$i) . ') ' . get_class($compiler);
        }
        $n = count($compilers);
        $this->writeln(
            'Building the project with the following ' .
                \Yoga\Strings::service()->ending($n, 'compiler') . ':' . $s
        );
        $this->progressBarStart($n);
        foreach ($compilers as $compiler) {
            $compiler->writeCompiledResult();
            $this->progressBarAdvance();
        }
    }

    /**
     * @return \Yoga\Compiler[]
     */
    private function getCompilers() {
        return \Yoga\Compiler\Finder::service()->findCompilers();
    }

}