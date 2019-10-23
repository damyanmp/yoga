<?php

namespace Yoga;

/**
 * @method static Logger service()
 */
class Logger extends Service {

    // When log file size reaches this value (in bytes), the current file is
    // renamed to <filename>-<datetime>.log and a new log file is started.
    private $maxLogFileSize = 10000000;

    private $debugLogName = 'debug';

    /**
     * @var string
     */
    private $directory;

    public function log($message, $logName) {
        $message = preg_replace("~[\\x00-\\x09\\x0b\\x0c\\x0e-\\x1f]~", '', $message);
        if (!$message) {
            return;
        }
        $message = (new DateTime)->format('Y-m-d H:i:s', true) . ' ' . $message;
        $logFullPath = $this->getLogFullPath($logName);
        $isFileFound = file_exists($logFullPath);
        if (
            $isFileFound
            && filesize($logFullPath) > $this->maxLogFileSize
        ) {
            $rename = $this->getLogFullPath(
                $logName . '-' . (new DateTime(time()))->format('Ymd-His')
            );
            if (!file_exists($rename)) {
                rename($logFullPath, $rename);
            }
        }
        $f = fopen($logFullPath, 'a');
        flock($f, LOCK_EX);
        fseek($f, 0, SEEK_END);
        fwrite($f, $message . PHP_EOL);
        fclose($f);
        if (!$isFileFound) {
            chmod($logFullPath, 0666);
        }
    }

    public function debug(
        $variable,
        $logName = null,
        $isBacktraceRequired = true,
        $nestedLevels = 8,
        $isHttpRequestInfoRequired = true,
        $skipBacktraceLines = 1
    ) {
        if (!$logName) {
            $logName = $this->debugLogName;
        }
        $this->log(
            Debugger::service()->getDebugMessage(
                $variable,
                $isBacktraceRequired,
                $nestedLevels,
                $isHttpRequestInfoRequired,
                $skipBacktraceLines + 1
            ),
            $logName
        );
    }

    public function getDebugLogFullPath() {
        return $this->getLogFullPath($this->debugLogName);
    }

    /**
     * @param string $logName
     * @return string
     */
    private function getLogFullPath($logName) {
        return $this->getDirectory() . $logName . '.log';
    }

    /**
     * @param string $directory
     * @return $this
     */
    public function setDirectory($directory) {
        $this->directory = $directory;
        return $this;
    }

    public function getDirectory() {
        return $this->directory;
    }

}