<?php

namespace Yoga\Selection\Sql;

class Resultset {

    /**
     * @var \Phalcon\Db\Result\Pdo
     */
    private $phalconResultset;

    /**
     * @var callable
     */
    private $transformRecordCallback;

    public function __construct(
        \Phalcon\Db\Result\Pdo $phalconResultset,
        callable $transformRecordCallback
    ) {
        $this->phalconResultset = $phalconResultset;
        $this->transformRecordCallback = $transformRecordCallback;
    }

    public function fetch() {
        $record = $this->phalconResultset->fetch();
        if (!$record) {
            return $record;
        }
        return call_user_func($this->transformRecordCallback, $record);
    }

}