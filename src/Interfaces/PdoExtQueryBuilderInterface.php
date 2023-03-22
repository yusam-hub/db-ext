<?php

namespace YusamHub\DbExt\Interfaces;
interface PdoExtQueryBuilderInterface
{
    const TAB = "[:tab]";
    const TAB_LEN = 2;
    const SPACE = " ";
    const SAFE_CHAR = "`";
    const OPERAND_EQUAL = '=';
    const OPERAND_MORE = '>';
    const OPERAND_MORE_EQUAL = '>=';
    const OPERAND_LESS_EQUAL = '<=';
    const OPERAND_LESS = '<';
    const OPERAND_LIKE = 'like';
    const OPERAND_BETWEEN = 'between';
    const OPERAND_IN = 'in';
    const OPERAND_LIST = [
        self::OPERAND_EQUAL,
        self::OPERAND_MORE,
        self::OPERAND_MORE_EQUAL,
        self::OPERAND_LESS_EQUAL,
        self::OPERAND_LESS,
        self::OPERAND_LIKE,
        self::OPERAND_BETWEEN,
        self::OPERAND_IN,
    ];
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