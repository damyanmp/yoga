<?php

namespace Yoga\Task\Cron\Schedule;

class FewTimesPerDay extends \Yoga\Task\Cron\Schedule {

    /**
     * Array of times in format ['00:00', '12:30', '16:20']
     * All times are UTC
     * @var string[]
     */
    private $times;

    /**
     * @param \Yoga\DateTime $lastRunDateTime
     * @throws \Exception
     * @return \Yoga\DateTime
     */
    public function getNextRunDateTime(\Yoga\DateTime $lastRunDateTime = null) {
        $now = (new \Yoga\DateTime);
        if (!$lastRunDateTime) {
            $lastRunDateTime = $now->addMinutes(-5);
        }
        $year = $now->getYear();
        $month = $now->getMonth();
        $day = $now->getDay();
        $bestSeconds = $bestDateTime = null;
        foreach ($this->times as $time) {
            $a = explode(':', $time);
            if (count($a) != 2) {
                throw new \Exception('Invalid format for time, expected HH:MM, got `' . $time . '`');
            }
            $hour = $a[0];
            $minute = $a[1];
            $dateTime = \Yoga\DateTime::createFromElements($year, $month, $day, $hour, $minute);
            $secondsSinceLastRun = $lastRunDateTime->getDiff($dateTime, \Yoga\DateTime\DatePart::SECOND);
            if ($secondsSinceLastRun <= 0) {
                continue;
            }
            if ((null === $bestSeconds) || $secondsSinceLastRun < $bestSeconds) {
                $bestSeconds = $secondsSinceLastRun;
                $bestDateTime = $dateTime;
            }
        }
        return $bestDateTime;
    }

    /**
     * @param \string[] $times
     * @return FewTimesPerDay
     */
    public function setTimes(array $times) {
        $this->times = $times;
        return $this;
    }

    /**
     * @return \string[]
     */
    public function getTimes() {
        return $this->times;
    }


}