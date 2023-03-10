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

    protected string $lastSql = '';
    protected array $lastBindings = [];
    protected ?\Closure $onDebugLogCallback = null;

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
     * @param \Closure|null $callback
     * @return void
     */
    public function onDebugLogCallback(?\Closure $callback): void
    {
        $this->onDebugLogCallback = $callback;
    }

    /**
     * @return string
     */
    public function getLastSql(): string
    {
        return $this->lastSql;
    }

    /**
     * @return array
     */
    public function getLastBindings(): array
    {
        return $this->lastBindings;
    }

    /**
     * @param string $query
     * @param array $options
     * @return false|\PDOStatement
     */
    protected function pdoPrepare(string $query, array $options = [])
    {
        try {
            return $this->pdo->prepare($query, $options);
        } catch (\PDOException $e) {
            $newE = new PdoExtException($this, $e->getMessage(), $e->getCode(), $e);
            $this->debugLog($newE->getMessage(), $newE->getData());
            throw $newE;
        }
    }

    /**
     * @param $params
     * @return bool
     */
    protected function pdoStatementExecute($params = null): bool
    {
        try {
            return $this->pdoStatement->execute($params);
        } catch (\PDOException $e) {
            $newE = new PdoExtException($this, $e->getMessage(), $e->getCode(), $e);
            $this->debugLog($newE->getMessage(), $newE->getData());
            throw $newE;
        }
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function debugLog(string $message, array $context = []): void
    {
        if (!$this->isDebugging) return;

        if (!is_null($this->onDebugLogCallback)) {
            $callback = $this->onDebugLogCallback;
            $callback($message, $context);
            return;
        }

        echo $message;
        if (!empty($context)) {
            echo json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        echo PHP_EOL;
    }

    /**
     * @param string $sql
     * @param array $bindings
     * @param \Closure|null $callbackRow
     * @param string|null $fetchClass
     * @return array
     */
    public function fetchAll(
        string $sql,
        array $bindings = [],
        ?\Closure $callbackRow = null,
        ?string $fetchClass = null): array
    {
        $this->lastSql = $sql;
        $this->lastBindings = $bindings;

        $this->debugLog($this->lastSql, $this->lastBindings);

        if (is_null($callbackRow)) {
            $this->pdoStatement = $this->pdoPrepare($this->lastSql);
        } else {
            $this->pdoStatement = $this->pdoPrepare($this->lastSql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);
        }

        if ($this->pdoStatement !== false && $this->pdoStatementExecute($this->lastBindings)) {
            if (is_null($callbackRow)) {
                if (class_exists($fetchClass)) {
                    $this->pdoStatement->setFetchMode(\PDO::FETCH_CLASS, $fetchClass);
                }
                $result = $this->pdoStatement->fetchAll(class_exists($fetchClass) ? \PDO::FETCH_CLASS: \PDO::FETCH_ASSOC);
                if (is_array($result)) {
                    return $result;
                }
            } else {
                $rows = [];
                if (class_exists($fetchClass)) {
                    $this->pdoStatement->setFetchMode(\PDO::FETCH_CLASS, $fetchClass);
                }
                while($row = $this->pdoStatement->fetch(class_exists($fetchClass) ? \PDO::FETCH_CLASS: \PDO::FETCH_ASSOC)) {
                    if (is_array($row) || is_object($row)) {
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
     * @param string|null $fetchClass
     * @return null|array|object
     */
    public function fetchOne(
        string $sql,
        array $bindings = [],
        ?string $fetchClass = null)
    {
        $this->lastSql = $sql;
        $this->lastBindings = $bindings;

        $this->debugLog($this->lastSql, $this->lastBindings);

        $this->pdoStatement = $this->pdoPrepare($this->lastSql);

        if ($this->pdoStatement !== false && $this->pdoStatementExecute($this->lastBindings)) {
            if (class_exists($fetchClass)) {
                $this->pdoStatement->setFetchMode(\PDO::FETCH_CLASS, $fetchClass);
            }
            $row = $this->pdoStatement->fetch(class_exists($fetchClass) ? \PDO::FETCH_CLASS: \PDO::FETCH_ASSOC);
            if (is_array($row) || is_object($row)) {
                return $row;
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
    public function lastInsertId(): ?int
    {
        $result = $this->pdo->lastInsertId();
        if ($result !== false) {
            return intval($result);
        }
        return null;
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
        $this->lastSql = $sql;
        $this->lastBindings = $bindings;

        $this->debugLog($this->lastSql, $this->lastBindings);

        $this->pdoStatement = $this->pdoPrepare($this->lastSql);

        if ($this->pdoStatement !== false && $this->pdoStatementExecute($this->lastBindings)) {
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

        $sql = $command . ' INTO `' . $tableName . '`  (' . implode(', ', $fields) . ') VALUES(' . implode(',', $values).')';

        return $this->exec($sql, $bindings);
    }

    /**
     * @param string $tableName
     * @param array $fieldValues
     * @return int|null
     */
    public function insertReturnId(string $tableName, array $fieldValues): ?int
    {
        if ($this->insert($tableName, $fieldValues)) {
            return $this->lastInsertId();
        }
        return null;
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

        $sql = 'UPDATE `' . $tableName . "` SET " . implode(", ", $sets) . ((!empty($where)) ? " WHERE " . implode(" AND ", $where) : '');

        if (is_int($limit)) {
            $sql .= " LIMIT " . $limit;
        }

        return $this->exec($sql, $bindings);
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

        return $this->exec($sql, $bindings);
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
