<?php

namespace Yoga\Task\Cron\Schedule;

class EveryFewMinutes extends \Yoga\Task\Cron\Schedule {

    /**
     * @var int
     */
    private $minutes;

    /**
     * @param \Yoga\DateTime $lastRunDateTime
     * @return \Yoga\DateTime
     */
    public function getNextRunDateTime(\Yoga\DateTime $lastRunDateTime = null) {
        if (!$lastRunDateTime) {
            return new \Yoga\DateTime;
        }
        return $lastRunDateTime->addMinutes($this->minutes);
    }

    /**
     * @param int $minutes
     * @return EveryFewMinutes
     */
    public function setMinutes($minutes) {
        $this->minutes = $minutes;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinutes() {
        return $this->minutes;
    }

}