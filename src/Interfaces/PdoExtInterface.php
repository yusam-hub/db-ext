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

    function concatDatabaseNameTableName(string $databaseName, string $tableName): string;
    function insert(string $databaseName, string $tableName, array $fieldValues, string $command = self::COMMAND_INSERT): bool;
    function insertReturnId(string $databaseName, string $tableName, array $fieldValues): ?int;
    function replace(string $databaseName, string $tableName, array $fieldValues): bool;
    function update(string $databaseName, string $tableName, array $fieldValues, $whereStatementOrWhereArray = null, ?int $limit = null): bool;
    function delete(string $databaseName, string $tableName, $whereStatementOrWhereArray = null, ?int $limit = null): bool;

    function lastInsertId(): ?int;
    function affectedRows(): int;

    function findModel(string $classModel, string $databaseName, string $tableName, string $pkKey, $pkVal): ?object;
    function findModelByAttributes(string $classModel, string $databaseName, string $tableName, array $attributes): ?object;
    function beginTransaction(): bool;
    function commitTransaction(): bool;
    function rollBackTransaction(): bool;
    function beginTransactionDepth(): bool;
    function commitTransactionDepth(): bool;
    function rollBackTransactionDepth(): bool;
    function withTransaction(\Closure $callback, bool $handleRollBackException): bool;
    public function queryBuilder(): PdoExtQueryBuilderInterface;
}