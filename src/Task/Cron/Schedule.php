<?php

namespace Yoga\Task\Cron;

abstract class Schedule {

    /**
     * @param \Yoga\DateTime $lastRunDateTime
     * @return \Yoga\DateTime
     */
    abstract public function getNextRunDateTime(\Yoga\DateTime $lastRunDateTime = null);

}