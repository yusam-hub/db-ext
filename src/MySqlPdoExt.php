<?php

namespace YusamHub\DbExt;

class MySqlPdoExt
{
    static public bool $DEBUG = false;
    const COMMAND_INSERT = 'INSERT';
    const COMMAND_INSERT_IGNORE = 'INSERT IGNORE';
    const COMMAND_REPLACE = 'REPLACE';

    protected \PDO $pdo;
    protected false|\PDOStatement $pdoStatement = false;

    /**
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return \PDO
     */
    protected function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * @return false|\PDOStatement
     */
    protected function PDOStatement(): false|\PDOStatement
    {
        return $this->pdoStatement;
    }

    /**
     * @param string $sql
     * @param array $bindings
     * @return void
     */
    protected function debugLog(string $sql, array $bindings): void
    {
        if (!self::$DEBUG) return;

        echo $sql;
        if (!empty($bindings)) {
            echo json_encode($bindings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        echo PHP_EOL;
    }

    /**
     * @param string $sql
     * @param array $bindings
     * @param \Closure|null $callbackRow
     * @return array
     */
    public function fetchAll(
        string $sql,
        array $bindings = [],
        ?\Closure $callbackRow = null): array
    {
        $this->debugLog($sql, $bindings);

        if (is_null($callbackRow)) {
            $this->pdoStatement = $this->pdo->prepare($sql);
        } else {
            $this->pdoStatement = $this->pdo->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
        }

        if ($this->pdoStatement !== false && $this->pdoStatement->execute($bindings)) {
            if (is_null($callbackRow)) {
                $result = $this->pdoStatement->fetchAll(\PDO::FETCH_ASSOC);
                if (is_array($result)) {
                    return $result;
                }
            } else {
                $rows = [];
                while($row = $this->pdoStatement->fetch(\PDO::FETCH_ASSOC)) {
                    if (is_array($row)) {
                        $rows[] = $callbackRow($row);
                    }
                }
                return $rows;
            }
        }
        return [];
    }

    /**
     * @param string $sql
     * @param array $bindings
     * @return null|array
     */
    public function fetchOne(
        string $sql,
        array $bindings = []): ?array
    {
        $this->debugLog($sql, $bindings);

        $this->pdoStatement = $this->pdo->prepare($sql);

        if ($this->pdoStatement !== false && $this->pdoStatement->execute($bindings)) {
            $ret = $this->pdoStatement->fetch(\PDO::FETCH_ASSOC);
            if (is_array($ret)) {
                return $ret;
            }
        }
        return null;
    }

    /**
     * @param string $sql
     * @param string $columnName
     * @param array $bindings
     * @param string|null $defaultValue
     * @return string|null
     */
    public function fetchOneColumn(
        string $sql,
        string $columnName,
        array $bindings = [],
        ?string $defaultValue = null
    ): ?string
    {
        $result = $this->fetchOne($sql, $bindings);

        if (is_array($result) && in_array($columnName, array_keys($result)) && !is_null($result[$columnName])) {
            return strval($result[$columnName]);
        }

        return $defaultValue;
    }

    /**
     * @return string
     */
    public function selectDateTime(): string
    {
        return $this->fetchOneColumn("SELECT NOW() AS dt", 'dt');
    }

    /**
     * @param string $value
     * @return bool
     */
    public function isDateTime(string $value): bool
    {
        return preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/', $value);
    }

    /**
     * @param string $value
     * @return bool
     */
    public function isDate(string $value): bool
    {
        return preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $value);
    }

    /**
     * @param string $sql
     * @param array $bindings
     * @return int
     */
    public function exec(string $sql,
                  array $bindings = []): int
    {
        $this->debugLog($sql, $bindings);

        $this->pdoStatement = $this->pdo->prepare($sql);

        if ($this->pdoStatement !== false && $this->pdoStatement->execute($bindings)) {
            return $this->pdoStatement->rowCount();
        }

        return 0;
    }

    /**
     * @param string $tableName
     * @param array $fieldValues
     * @param string|array|null $whereStatementOrWhereArray
     * @return int
     */
    public function update(string $tableName, array $fieldValues, string|array|null $whereStatementOrWhereArray = null): int
    {
        $bindings = [];
        $sets = [];
        foreach ($fieldValues as $field => $value) {
            $sets[] = $field . ' = :' . $field;
            $bindings[':'.$field] = $value;
        }

        $where = [];
        if (is_array($whereStatementOrWhereArray)) {
            foreach ($whereStatementOrWhereArray as $field => $value) {
                $where[] = $field . ' = :' . $field;
                $bindings[':'.$field] = $value;
            }
        } elseif (is_string($whereStatementOrWhereArray)) {
            $where[] = $whereStatementOrWhereArray;
        }

        $sql = 'UPDATE ' . $tableName . " SET " . implode(", ", $sets) . ((!empty($where)) ? " WHERE " . implode(" AND ", $where) : '');

        $this->debugLog($sql, $bindings);

        $this->pdoStatement = $this->pdo->prepare($sql);

        if ($this->pdoStatement !== false && $this->pdoStatement->execute($bindings)) {
            return $this->pdoStatement->rowCount();
        }

        return 0;
    }

    /**
     * @param string $tableName
     * @param array $fieldValues
     * @param string $command
     * @param bool $returnLastInsertId
     * @return int
     */
    public function insert(string $tableName, array $fieldValues, string $command = self::COMMAND_INSERT, bool $returnLastInsertId = false): int
    {
        $bindings = [];
        $fields = [];
        $values = [];
        foreach ($fieldValues as $field => $value) {
            $fields[] = $field;
            $values[] = ":" . $field;
            $bindings[':'.$field] = $value;
        }

        $sql = $command . ' INTO ' . $tableName . '  (' . implode(', ', $fields) . ') VALUES(' . implode(',', $values).')';

        $this->debugLog($sql, $bindings);

        $this->pdoStatement = $this->pdo->prepare($sql);

        if ($this->pdoStatement !== false && $this->pdoStatement->execute($bindings)) {
            if ($returnLastInsertId) {
                return $this->pdo->lastInsertId();
            }
            return 1;
        }

        return 0;
    }

    /**
     * @param string $tableName
     * @param array $fieldValues
     * @return int
     */
    public function replace(string $tableName, array $fieldValues): int
    {
        return $this->insert($tableName, $fieldValues, self::COMMAND_REPLACE);
    }


    /**
     * @param string $tableName
     * @param string|array|null $whereStatementOrWhereArray
     * @return int
     */
    public function delete(string $tableName, string|array|null $whereStatementOrWhereArray = null): int
    {
        $bindings = [];

        $where = [];
        if (is_array($whereStatementOrWhereArray)) {
            foreach ($whereStatementOrWhereArray as $field => $value) {
                $where[] = $field . ' = :' . $field;
                $bindings[':'.$field] = $value;
            }
        } elseif (is_string($whereStatementOrWhereArray)) {
            $where[] = $whereStatementOrWhereArray;
        }

        $sql = 'DELETE FROM ' . $tableName . ((!empty($where)) ? " WHERE " . implode(" AND ", $where) : '');

        $this->debugLog($sql, $bindings);

        $this->pdoStatement = $this->pdo->prepare($sql);

        if ($this->pdoStatement !== false && $this->pdoStatement->execute($bindings)) {
            return $this->pdoStatement->rowCount();
        }

        return 0;
    }

    /**
     * @param \Closure $callback
     * @param bool $handleRollBackException
     * @return bool
     * @throws \Throwable
     */
    public function withTransaction(\Closure $callback, bool $handleRollBackException): bool
    {
        $this->pdo->beginTransaction();

        try {

            try {
                $callback();

                $success = true;

            } catch (\Throwable $e) {

                $success = false;

                if ($handleRollBackException) {
                    throw $e;
                }

            }

        } finally {

            if ($success) {

                $this->pdo->commit();

            } else {

                $this->pdo->rollBack();

            }

        }

        return $success;
    }

    /**
     * @param string $name
     * @param int $timeout
     */
    public function lockBegin(string $name, int $timeout = 10): void
    {
        $this->exec("SELECT GET_LOCK('" . md5($name) . "'," . $timeout . ")");
    }

    /**
     * @param string $name
     */
    public function lockEnd(string $name): void
    {
        $this->exec("SELECT RELEASE_LOCK('" .  md5($name) . "')");
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isLocked(string $name): bool
    {
        $isFreeLock = $this->fetchOneColumn("SELECT IS_FREE_LOCK('" .  md5($name) . "') as isFreeLock", 'isFreeLock');
        return !is_null($isFreeLock) && !intval($isFreeLock);
    }
}
