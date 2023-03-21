<?php

namespace YusamHub\DbExt\Interfaces;
interface PdoExtQueryBuilderInterface
{
    function getSql(): string;
    public function select($expression): PdoExtQueryBuilderInterface;
    public function from($reference): PdoExtQueryBuilderInterface;
    public function where($condition): PdoExtQueryBuilderInterface;
    public function andWhere($condition): PdoExtQueryBuilderInterface;
    public function orWhere($condition): PdoExtQueryBuilderInterface;
    public function groupBy($expression): PdoExtQueryBuilderInterface;
    public function addGroupBy($expression): PdoExtQueryBuilderInterface;
    public function having($condition): PdoExtQueryBuilderInterface;
    public function addHaving($condition): PdoExtQueryBuilderInterface;
    public function orderBy($expression): PdoExtQueryBuilderInterface;
    public function addOrderBy($expression): PdoExtQueryBuilderInterface;
    public function offset(int $offset): PdoExtQueryBuilderInterface;
    public function limit(int $limit): PdoExtQueryBuilderInterface;
}