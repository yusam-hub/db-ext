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
    public function select($expression): PdoExtQueryBuilderInterface
    {
        return $this;
    }
    public function from($tableReferences): PdoExtQueryBuilderInterface
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
    public function orderBy($expression): PdoExtQueryBuilderInterface
    {
        return $this;
    }
    public function addOrderBy($expression): PdoExtQueryBuilderInterface
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
    public function getSql(): string
    {
        return '';
    }
    public function getBindings(): array
    {
        return [];
    }
    public function fetchAll(?\Closure $callbackRow = null, ?string $fetchClass = null): array
    {
        return $this->pdoExt->fetchAll($this->getSql(), $this->getBindings(), $callbackRow, $fetchClass);
    }
    public function fetchOne(?string $fetchClass = null)
    {
        return $this->pdoExt->fetchOne($this->getSql(), $this->getBindings(), $fetchClass);
    }
    public function fetchOneColumn(string $columnName, ?string $defaultValue = null): ?string
    {
        return $this->pdoExt->fetchOneColumn($this->getSql(), $columnName, $this->getBindings(), $defaultValue);
    }
}