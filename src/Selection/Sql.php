<?php

namespace Yoga\Selection;

abstract class Sql extends \Yoga\Selection {

    /**
     * @return string
     */
    abstract protected function getSelectSql();

    /**
     * @return string
     */
    abstract protected function getFromSql();

    /**
     * @return string
     */
    protected function getWhereSql() {
        return '';
    }

    protected function getOrderSql() {
        if (!$this->getOrder()) {
            return '';
        }
        return $this->getOrder() > 0 ? $this->getOrder() : -$this->getOrder() . ' DESC';
    }

    protected function getGroupSql() {
        return '';
    }

    protected function loadRecords() {
        return \Yoga\Sql::service()->select($this->getFullSql());
    }

    private function getFullSql() {
        return 'SELECT ' . $this->getSelectSql() .
            $this->getFromSqlFullLine() .
            $this->getWhereSqlFullLine() .
            $this->getGroupSqlFullLine() .
            $this->getOrderSqlFullLine() .
            $this->getLimitSqlFullLine();
    }

    final public function selectUnbuffered() {
        return new \Yoga\Selection\Sql\Resultset(
            \Yoga\Sql::service()->selectUnbuffered($this->getFullSql()),
            function ($record) {
                return $this->transformRecord($record);
            }
        );
    }

    protected function getLimitSqlFullLine() {
        if ($this->getIsAll()) {
            return '';
        }
        $recordsPerPage = $this->getRecordsPerPage()
            ?: \Yoga\Configuration::service()->getDefaultRecordsPerPage();
        $limitStart = $this->getPage() > 1
            ? ($this->getPage() - 1) * $recordsPerPage
            : 0;
        return ' LIMIT ' . $limitStart . ', ' . $recordsPerPage;
    }

    /**
     * @throws \Exception
     * @return \Yoga\Selection\Stats
     */
    public function getStats() {
        if ($groupBy = $this->getGroupSql()) {
            if (strpos($groupBy, ',')) {
                throw new \Exception(
                    'Please override `' . get_called_class() .
                    '::getStats()` - unable to retrive record count automatically'
                );
            }
            $query = 'SELECT COUNT(DISTINCT ' . $groupBy . ')' .
                $this->getFromSqlFullLine() .
                $this->getWhereSqlFullLine();
        } else {
            $query = 'SELECT COUNT(*)' .
                $this->getFromSqlFullLine() .
                $this->getWhereSqlFullLine();
        }
        $recordCount = (int)\Yoga\Sql::service()->select1value($query);
        return (new \Yoga\Selection\Stats)
            ->setRecordCount($recordCount);
    }

    private function getFromSqlFullLine() {
        return ' FROM ' . $this->getFromSql();
    }

    private function getWhereSqlFullLine() {
        return ($whereSql = $this->getWhereSql()) ? ' WHERE ' . $whereSql : '';
    }

    private function getGroupSqlFullLine() {
        return ($groupSql = $this->getGroupSql()) ? ' GROUP BY ' . $groupSql : '';
    }

    private function getOrderSqlFullLine() {
        return ($orderSql = $this->getOrderSql()) ? ' ORDER BY ' . $orderSql : '';
    }

}