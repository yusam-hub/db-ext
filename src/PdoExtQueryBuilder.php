<?php

namespace YusamHub\DbExt;

use YusamHub\DbExt\Interfaces\PdoExtQueryBuilderInterface;

class PdoExtQueryBuilder implements PdoExtQueryBuilderInterface
{
    protected PdoExt $pdoExt;

    public function __construct(PdoExt $pdoExt)
    {
        $this->pdoExt = $pdoExt;
    }
    function getSql(): string
    {
        return '';
    }
    public function select($expression): PdoExtQueryBuilderInterface
    {
        return $this;
    }
    public function from($reference): PdoExtQueryBuilderInterface
    {
        return $this;
    }
    public function where($condition): PdoExtQueryBuilderInterface
    {
        return $this;
    }
    public function andWhere($condition): PdoExtQueryBuilderInterface
    {
        return $this;
    }
    public function orWhere($condition): PdoExtQueryBuilderInterface
    {
        return $this;
    }
    public function groupBy($expression): PdoExtQueryBuilderInterface
    {
        return $this;
    }
    public function addGroupBy($expression): PdoExtQueryBuilderInterface
    {
        return $this;
    }
    public function having($condition): PdoExtQueryBuilderInterface
    {
        return $this;
    }
    public function addHaving($condition): PdoExtQueryBuilderInterface
    {
        return $this;
    }
    public function orderBy($columns): PdoExtQueryBuilderInterface
    {
        return $this;
    }
    public function addOrderBy($columns): PdoExtQueryBuilderInterface
    {
        return $this;
    }
    public function offset(int $offset): PdoExtQueryBuilderInterface
    {
        return $this;
    }
    public function limit(int $limit): PdoExtQueryBuilderInterface
    {
        return $this;
    }
}