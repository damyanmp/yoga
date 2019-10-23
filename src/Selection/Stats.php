<?php

namespace Yoga\Selection;

class Stats {

    /**
     * @var int
     */
    private $recordCount;

    /**
     * @param int $recordCount
     * @return Stats
     */
    public function setRecordCount($recordCount) {
        $this->recordCount = $recordCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getRecordCount() {
        return $this->recordCount;
    }

}