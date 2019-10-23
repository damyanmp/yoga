<?php

namespace Yoga\Task;

abstract class Cron extends \Yoga\Task {

    /**
     * @return \Yoga\Task\Cron\Schedule
     */
    abstract protected function getSchedule();

    final public function handleIfDue() {
        if (!$this->isDue()) {
            return;
        }
        $now = (new \Yoga\DateTime);
        \Yoga\Cache::service()
            ->set($this->getKey(), $now->getTimestamp());
        $this->handle();
    }

    /**
     * @return boolean
     */
    public function isDue() {
        $lastRunTimestamp = \Yoga\Cache::service()->get($this->getKey());
        if ($lastRunTimestamp) {
            $lastRunDateTime = new \Yoga\DateTime($lastRunTimestamp);
        } else {
            $lastRunDateTime = null;
        }
        $schedule = $this->getSchedule();
        if (!$schedule) {
            return false;
        }
        $nextRunDateTime = $schedule->getNextRunDateTime($lastRunDateTime);
        if (!$nextRunDateTime) {
            return false;
        }
        $now = new \Yoga\DateTime;
        return $now->getTimestamp() >= $nextRunDateTime->getTimestamp();
    }

    private function getKey() {
        return get_called_class() . '-lastRunTimestamp';
    }

}