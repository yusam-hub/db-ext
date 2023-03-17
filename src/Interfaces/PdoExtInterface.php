<?php

namespace YusamHub\DbExt\Interfaces;

interface PdoExtInterface
{
    const COMMAND_INSERT = 'INSERT';
    const COMMAND_INSERT_IGNORE = 'INSERT IGNORE';
    const COMMAND_REPLACE = 'REPLACE';

    function getPdo(): \PDO;
    function onDebugLogCallback(?\Closure $callback): void;
    function getLastSql(): string;
    function getLastBindings(): array;

    function fetchAll(string $sql, array $bindings = [], ?\Closure $callbackRow = null, ?string $fetchClass = null): array;
    function fetchOne(string $sql, array $bindings = [], ?string $fetchClass = null);
    function fetchOneColumn(string $sql, string $columnName, array $bindings = [], ?string $defaultValue = null): ?string;

    function escape(?string $value, bool $trim = true): string;
    function exec(string $sql, array $bindings = []): bool;

    function insert(string $tableName, array $fieldValues, string $command = self::COMMAND_INSERT): bool;
    function insertReturnId(string $tableName, array $fieldValues): ?int;
    function replace(string $tableName, array $fieldValues): bool;
    function update(string $tableName, array $fieldValues, $whereStatementOrWhereArray = null, ?int $limit = null): bool;
    function delete(string $tableName, $whereStatementOrWhereArray = null, ?int $limit = null): bool;

    function lastInsertId(): ?int;
    function affectedRows(): int;

    function findModel(string $classModel, string $tableName, string $pkKey, $pkVal): ?object;
    function findModelByAttributes(string $classModel, string $tableName, array $attributes): ?object;
    function beginTransaction(): bool;
    function commitTransaction(): bool;
    function rollBackTransaction(): bool;
    function withTransaction(\Closure $callback, bool $handleRollBackException): bool;
}