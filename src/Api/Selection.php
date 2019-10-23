<?php

namespace Yoga\Api;

abstract class Selection extends \Yoga\Api {

    /**
     * @var int
     */
    public $order;

    /**
     * @var int
     */
    public $page;

    /**
     * @var boolean
     */
    public $isStats;

    /**
     * @var boolean
     */
    public $isAll;

    /**
     * @return \Yoga\Selection
     */
    abstract protected function getSelection();

    public function handle() {
        $selection = $this->getSelection();
        if (!$selection) {
            return null;
        }
        if ($this->isStats) {
            return $selection->getStats();
        }
        $result = $selection
            ->setPage($this->page)
            ->setOrder($this->order)
            ->setIsAll($this->isAll)
            ->select();
        return $result;
    }

}