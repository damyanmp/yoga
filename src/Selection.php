<?php

namespace Yoga;

abstract class Selection extends \Yoga\Service {

    /**
     * @var int
     */
    private $order;

    /**
     * @var int
     */
    private $page;

    /**
     * @var boolean
     */
    private $isAll;

    /**
     * @var int
     */
    private $recordsPerPage;

    /**
     * @return array
     */
    final public function select() {
        $result = $this->loadRecords();
        foreach ($result as &$record) {
            $record = $this->transformRecord($record);
        }
        return $result;
    }

    /**
     * @return array
     */
    abstract protected function loadRecords();

    protected function transformRecord(array $record) {
        // typecast fields, wrap into a model object etc.
        return $record;
    }

    /**
     * @return \Yoga\Selection\Stats
     */
    abstract public function getStats();

    /**
     * @param int $order
     * @return Selection
     */
    public function setOrder($order) {
        $this->order = $order;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * @param int $page
     * @return Selection
     */
    public function setPage($page) {
        $this->page = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getPage() {
        return $this->page;
    }

    /**
     * @return boolean
     */
    public function getIsAll() {
        return $this->isAll;
    }

    /**
     * @param boolean $isAll
     * @return Selection
     */
    public function setIsAll($isAll) {
        $this->isAll = $isAll;
        return $this;
    }

    /**
     * @return int
     */
    public function getRecordsPerPage() {
        return $this->recordsPerPage;
    }

    /**
     * @param int $recordsPerPage
     * @return $this
     */
    public function setRecordsPerPage($recordsPerPage) {
        $this->recordsPerPage = $recordsPerPage;
        return $this;
    }

}