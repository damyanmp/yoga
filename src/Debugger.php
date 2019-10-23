<?php

namespace Yoga;

/**
 * @method static Debugger service()
 */
class Debugger extends Service {

    public function getDebugMessage(
        $variable,
        $isBacktraceRequired = true,
        $nestedLevels = 8,
        $isHttpRequestInfoRequired = true,
        $skipBacktraceLines = 1
    ) {
        if (is_string($variable) || is_numeric($variable)) {
            $message = $variable;
        } else {
            $message = Formatter::service()
                ->format($variable, $nestedLevels);
        }
        if ($isBacktraceRequired) {
            $message .= ' [' . $this->getBacktrace($skipBacktraceLines) . ']';
        }
        if ($isHttpRequestInfoRequired && isset($_SERVER['REQUEST_URI'])) {
            $message .= ' ' . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'];
        }
        if (strlen($message) > 80) {
            $message .= PHP_EOL;
        }
        return $message;
    }

    private function getBacktrace($skipBacktraceLines) {
        $result = '';
        $phpBacktrace = debug_backtrace();
        $startIndex = $skipBacktraceLines;
        for ($i = $startIndex; $i < count($phpBacktrace); $i++) {
            $s = '';
            if (!empty($phpBacktrace[$i]['file'])) {
                if (
                    isset($phpBacktrace[$i - 1]['file'])
                    && $phpBacktrace[$i]['file'] == $phpBacktrace[$i - 1]['file']
                    && $phpBacktrace[$i]['line'] == $phpBacktrace[$i - 1]['line']
                ) {
                    continue;
                }
                $s .= $this->formatFilePath($phpBacktrace[$i]['file']);
                if (isset($phpBacktrace[$i]['line'])) {
                    $s .= ' ' . $phpBacktrace[$i]['line'];
                }
            }
            if ($s) {
                if ($result) {
                    $result .= ' - ';
                }
                $result .= $s;
            }
        }
        return $result;
    }

    private function formatFilePath($filePath) {
        $rootDirectoryFormatted = $this->getRootDirectoryFormatted();
        $filePathFormatted = Formatter::service()
            ->formatPath($filePath);
        if (
            substr($filePathFormatted, 0, strlen($rootDirectoryFormatted))
                === $rootDirectoryFormatted
        ) {
            return substr($filePathFormatted, strlen($rootDirectoryFormatted));
        }
        return $filePathFormatted;
    }

    /**
     * @return string
     */
    private function getRootDirectoryFormatted() {
        return ComputeOnce::service()->handle(function () {
            return Formatter::service()
                ->formatPath(\Yoga\Application::service()->getRootDirectory());
        });
    }

}