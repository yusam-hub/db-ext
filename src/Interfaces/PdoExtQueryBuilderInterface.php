<?php

namespace YusamHub\DbExt\Interfaces;
interface PdoExtQueryBuilderInterface
{
    function select($expression): PdoExtQueryBuilderInterface;
    function from($tableReferences): PdoExtQueryBuilderInterface;
    function where($condition): PdoExtQueryBuilderInterface;
    function andWhere($condition): PdoExtQueryBuilderInterface;
    function orWhere($condition): PdoExtQueryBuilderInterface;
    function groupBy($expression): PdoExtQueryBuilderInterface;
    function addGroupBy($expression): PdoExtQueryBuilderInterface;
    function having($condition): PdoExtQueryBuilderInterface;
    function addHaving($condition): PdoExtQueryBuilderInterface;
    function orderBy($expression): PdoExtQueryBuilderInterface;
    function addOrderBy($expression): PdoExtQueryBuilderInterface;
    function offset(int $offset): PdoExtQueryBuilderInterface;
    function limit(int $limit): PdoExtQueryBuilderInterface;
    function getSql(): string;
    function getBindings(): array;
    function fetchAll(?\Closure $callbackRow = null, ?string $fetchClass = null): array;
    function fetchOne(?string $fetchClass = null);
    function fetchOneColumn(string $columnName, ?string $defaultValue = null): ?string;
}