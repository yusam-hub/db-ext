<?php

namespace YusamHub\DbExt;

use YusamHub\DbExt\Traits\MySqlPdoExtTrait;

class PdoExt
{
    use MySqlPdoExtTrait;

    public bool $isDebugging = false;
    const COMMAND_INSERT = 'INSERT';
    const COMMAND_INSERT_IGNORE = 'INSERT IGNORE';
    const COMMAND_REPLACE = 'REPLACE';

    protected \PDO $pdo;

    /**
     * @var false|\PDOStatement
     */
    protected $pdoStatement = false;

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
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * @return \PDOStatement|false
     */
    protected function PDOStatement()
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
        if (!$this->isDebugging) return;

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
     * @param string|null $value
     * @param bool $trim
     * @return string
     */
    public function escape(?string $value, bool $trim = true): string
    {
        $value = strval($value);

        if ($trim) {
            $value = trim($value);
        }

        return str_replace("'","\'",$value);
    }

    /**
     * @return int
     */
    public function lastInsertId(): int
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * @return int
     */
    public function affectedRows(): int
    {
        return $this->pdoStatement->rowCount();
    }

    /**
     * @param string $sql
     * @param array $bindings
     * @return bool
     */
    public function exec(string $sql, array $bindings = []): bool
    {
        $this->debugLog($sql, $bindings);

        $this->pdoStatement = $this->pdo->prepare($sql);

        if ($this->pdoStatement !== false && $this->pdoStatement->execute($bindings)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $tableName
     * @param array $fieldValues
     * @param string $command
     * @return bool
     */
    public function insert(string $tableName, array $fieldValues, string $command = self::COMMAND_INSERT): bool
    {
        $bindings = [];
        $fields = [];
        $values = [];
        foreach ($fieldValues as $field => $value) {
            $fields[] = sprintf("`%s`",$field);
            $values[] = "?";
            $bindings[] = $value;
        }

        $sql = $command . ' INTO ' . $tableName . '  (' . implode(', ', $fields) . ') VALUES(' . implode(',', $values).')';

        $this->debugLog($sql, $bindings);

        $this->pdoStatement = $this->pdo->prepare($sql);

        if ($this->pdoStatement !== false && $this->pdoStatement->execute($bindings)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $tableName
     * @param array $fieldValues
     * @return int
     */
    public function insertReturnId(string $tableName, array $fieldValues): int
    {
        $this->insert($tableName, $fieldValues);
        return $this->lastInsertId();
    }

    /**
     * @param string $tableName
     * @param array $fieldValues
     * @return bool
     */
    public function replace(string $tableName, array $fieldValues): bool
    {
        return $this->insert($tableName, $fieldValues, self::COMMAND_REPLACE);
    }


    /**
     * @param string $tableName
     * @param array $fieldValues
     * @param string|array|null $whereStatementOrWhereArray
     * @param int|null $limit
     * @return bool
     */
    public function update(string $tableName, array $fieldValues, $whereStatementOrWhereArray = null, ?int $limit = null): bool
    {
        $bindings = [];
        $sets = [];
        foreach ($fieldValues as $field => $value) {
            $sets[] = sprintf("`%s`",$field) . ' = ?';
            $bindings[] = $value;
        }

        $where = [];
        if (is_array($whereStatementOrWhereArray)) {
            foreach ($whereStatementOrWhereArray as $field => $value) {
                $where[] = sprintf("`%s`",$field) . ' = ?';
                $bindings[] = $value;
            }
        } elseif (is_string($whereStatementOrWhereArray)) {
            $where[] = $whereStatementOrWhereArray;
        }

        $sql = 'UPDATE ' . $tableName . " SET " . implode(", ", $sets) . ((!empty($where)) ? " WHERE " . implode(" AND ", $where) : '');
        if (is_int($limit)) {
            $sql .= " LIMIT " . $limit;
        }

        $this->debugLog($sql, $bindings);

        $this->pdoStatement = $this->pdo->prepare($sql);

        if ($this->pdoStatement !== false && $this->pdoStatement->execute($bindings)) {
            return true;
        }

        return false;
    }


    /**
     * @param string $tableName
     * @param string|array|null $whereStatementOrWhereArray
     * @param int|null $limit
     * @return bool
     */
    public function delete(string $tableName, $whereStatementOrWhereArray = null, ?int $limit = null): bool
    {
        $bindings = [];

        $where = [];
        if (is_array($whereStatementOrWhereArray)) {
            foreach ($whereStatementOrWhereArray as $field => $value) {
                $where[] = sprintf("`%s`",$field) . ' = ?';
                $bindings[] = $value;
            }
        } elseif (is_string($whereStatementOrWhereArray)) {
            $where[] = $whereStatementOrWhereArray;
        }

        $sql = 'DELETE FROM ' . $tableName . ((!empty($where)) ? " WHERE " . implode(" AND ", $where) : '');
        if (is_int($limit)) {
            $sql .= " LIMIT " . $limit;
        }

        $this->debugLog($sql, $bindings);

        $this->pdoStatement = $this->pdo->prepare($sql);

        if ($this->pdoStatement !== false && $this->pdoStatement->execute($bindings)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commitTransaction(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * @return bool
     */
    public function rollBackTransaction(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * @param \Closure $callback
     * @param bool $handleRollBackException
     * @return bool
     * @throws \Throwable
     */
    public function withTransaction(\Closure $callback, bool $handleRollBackException): bool
    {
        $this->beginTransaction();

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

                $this->commitTransaction();

            } else {

                $this->rollBackTransaction();

            }

        }

        return $success;
    }

}
