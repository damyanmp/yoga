<?php

namespace Yoga\Console\Command;

/**
 * @Command("cron", description = "Run all cron tasks that are due")
 */
class Cron extends \Yoga\Console\Command {

    public function handle() {
        foreach ($this->getCronTasks() as $task) {
            $task->handleIfDue();
        }
    }

    /**
     * Scan Tasks directory and get instances of Recurring present in it
     * @return \Yoga\Task\Cron[]
     */
    private function getCronTasks() {
        $dir = \Yoga\Application::service()->getRootDirectory() . 'server/Task/Cron/';
        if (!file_exists($dir)) {
            return [];
        }
        $result = [];
        $iterator = new \DirectoryIterator($dir);
        foreach ($iterator as $fileInfo) {
            /** @var $fileInfo \DirectoryIterator */
            if ($fileInfo->isDot()) {
                continue;
            }
            $className = '\\Task\\Cron\\' . str_replace('.php', '', $fileInfo->getFilename());
            /** @var Cron $task */
            $task = call_user_func($className . '::service');
            if (!$task) {
                continue;
            }
            $result[] = $task;
        }
        return $result;
    }

}