<?php

namespace Yoga;

/**
 * @method static Sql service()
 */
class Sql extends \Yoga\Service {

    public function execute($query) {
        return $this->wrapSqlForBetterLogging($query, function () use ($query) {
            return $this->getPhalconConnection()->execute($query);
        });
    }

    public function getLastInsertId() {
        return (int)$this->getPhalconConnection()->lastInsertId();
    }

    public function getAffectedRowCount() {
        return $this->getPhalconConnection()->affectedRows();
    }

    public function insert($query) {
        return $this->wrapSqlForBetterLogging($query, function () use ($query) {
            $this->execute($query);
            return $this->getLastInsertId();
        });
    }

    public function select($query) {
        return $this->wrapSqlForBetterLogging($query, function () use ($query) {
            return $this->getPhalconConnection()->fetchAll($query, \Phalcon\Db::FETCH_ASSOC);
        });
    }

    /**
     * Use this one for large queries - it will establish a separated database
     * connection with the option to fetch records one by one, without buffering
     * (storing them in an in-memory array).
     * @param string $query
     * @return \Phalcon\Db\Result\Pdo
     * @throws \Exception
     */
    public function selectUnbuffered($query) {
        return $this->wrapSqlForBetterLogging($query, function () use ($query) {
            $result = $this->getPhalconConnection(false)->query($query);
            $result->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            return $result;
        });
    }

    public function select1($query) {
        return $this->wrapSqlForBetterLogging($query, function () use ($query) {
            return $this->getPhalconConnection()->fetchOne($query, \Phalcon\Db::FETCH_ASSOC);
        });
    }

    public function select1value($query) {
        return $this->wrapSqlForBetterLogging($query, function () use ($query) {
            $a = $this->getPhalconConnection()->fetchOne($query, \Phalcon\Db::FETCH_ASSOC);
            if (!is_array($a)) {
                return null;
            }
            return $a[array_keys($a)[0]];
        });
    }

    public function escapeString($s) {
        return $this->getPhalconConnection()->escapeString($s);
    }

    public function escapeDateTime(DateTime $dt = null) {
        if (!$dt) {
            return 'null';
        }
        return $this->escapeString($dt->formatMysql());
    }

    public function escapeInteger($integer) {
        if (null === $integer) {
            return 'null';
        }
        return (int)$integer;
    }

    public function escapeEnum(Enum $enum = null) {
        if (!$enum) {
            return 'null';
        }
        return (int)$enum->getValue();
    }

    /**
     * @param callable $callback
     * @throws \Exception
     * @return mixed
     */
    public function transaction(callable $callback) {
        $result = null;
        $this->getPhalconConnection()->begin(true);
        try {
            $result = $callback();
        } catch (\Exception $e) {
            $this->getPhalconConnection()->rollback(true);
            throw $e;
        }
        $this->getPhalconConnection()->commit(true);
        return $result;
    }

    public function escapeBoolean($boolean) {
        if (null === $boolean) {
            return 'null';
        }
        return $boolean ? 1 : 0;
    }

    protected function getConfiguration() {
        return Configuration::service()->getSqlConfiguration();
    }

    /**
     * @param boolean $isBuffered
     * @return \Phalcon\Db\Adapter\Pdo
     */
    private function getPhalconConnection($isBuffered = true) {
        static $connections = [true => null, false => null];
        if (!$connections[$isBuffered]) {
            $connections[$isBuffered] = $this->factory(
                $this->getConfiguration(),
                $isBuffered
            );
        }
        return $connections[$isBuffered];
    }

    private function factory(\Yoga\Configuration\Sql $configuration, $isBuffered) {
        if ($configuration instanceof \Yoga\Configuration\Sql\Mysql) {
            $pdoOptions = [
                'host' => $configuration->getHost(),
                'username' => $configuration->getUserName(),
                'password' => $configuration->getPassword(),
                'dbname' => $configuration->getDatabaseName(),
                'port' => $configuration->getPort()
            ];
            if (!$isBuffered) {
                $pdoOptions['options'] = [
                    \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
                ];
            }
            return new \Phalcon\Db\Adapter\Pdo\Mysql($pdoOptions);
        }
        throw new \Exception('Configuration not supported: `' . get_class($configuration) . '`');
    }

    /**
     * @param string $query
     * @param callable $callback
     * @throws \Exception
     * @return mixed
     */
    private function wrapSqlForBetterLogging($query, callable $callback) {
        try {
            return $callback();
        } catch (\Exception $e) {
            Logger::service()->debug('The following error (see right below) is caused by this sql:' . "\n" . $query);
            throw $e;
        }
    }

}