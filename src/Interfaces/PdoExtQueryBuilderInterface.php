<?php

namespace YusamHub\DbExt\Interfaces;
interface PdoExtQueryBuilderInterface
{
    const ORDER_BY_DEFAULT = '';
    const ORDER_BY_ASC = 'asc';
    const ORDER_BY_DESC = 'desc';
    const ORDER_BY_LIST = [
        self::ORDER_BY_DEFAULT,
        self::ORDER_BY_ASC,
        self::ORDER_BY_DESC,
    ];

    function select($expression): PdoExtQueryBuilderInterface;
    function from($tableReferences): PdoExtQueryBuilderInterface;
    function where($condition): PdoExtQueryBuilderInterface;
    function andWhere($condition): PdoExtQueryBuilderInterface;
    function orWhere($condition): PdoExtQueryBuilderInterface;
    function groupBy($expression): PdoExtQueryBuilderInterface;
    function having($condition): PdoExtQueryBuilderInterface;
    function orderBy($expression): PdoExtQueryBuilderInterface;
    function offset(int $offset): PdoExtQueryBuilderInterface;
    function limit(int $limit): PdoExtQueryBuilderInterface;
    function getSql(): string;
    function getBindings(): array;
    function fetchAll(?\Closure $callbackRow = null, ?string $fetchClass = null): array;
    function fetchOne(?string $fetchClass = null);
    function fetchOneColumn(string $columnName, ?string $defaultValue = null): ?string;
}