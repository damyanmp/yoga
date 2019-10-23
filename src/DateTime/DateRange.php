<?php

namespace Yoga\DateTime;

class DateRange {

    /**
     * @var \Yoga\DateTime
     */
    private $start;

    /**
     * @var \Yoga\DateTime
     */
    private $finish;

    /**
     * @param \Yoga\DateTime $finish
     * @return DateRange
     */
    public function setFinish(\Yoga\DateTime $finish = null) {
        $this->finish = $finish;
        return $this;
    }

    /**
     * @return \Yoga\DateTime
     */
    public function getFinish() {
        return $this->finish;
    }

    /**
     * @param \Yoga\DateTime $start
     * @return DateRange
     */
    public function setStart(\Yoga\DateTime $start = null) {
        $this->start = $start;
        return $this;
    }

    /**
     * @return \Yoga\DateTime
     */
    public function getStart() {
        return $this->start;
    }

}
