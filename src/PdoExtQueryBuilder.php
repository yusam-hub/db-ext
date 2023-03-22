<?php

namespace YusamHub\DbExt;

use YusamHub\DbExt\Interfaces\PdoExtQueryBuilderInterface;

class PdoExtQueryBuilder implements PdoExtQueryBuilderInterface
{
    const TAB = "[:tab]";
    const TAB_LEN = 2;
    const SPACE = " ";
    const SAFE_CHAR = "`";
    protected array $select = ["*"];
    protected array $from = [];
    protected array $where = [];
    protected array $groupBy = [];
    protected array $having = [];
    protected array $orderBy = [];
    protected ?int $offset = null;
    protected ?int $limit = null;
    protected array $bindings = [];

    protected PdoExt $pdoExt;
    public function __construct(PdoExt $pdoExt)
    {
        $this->pdoExt = $pdoExt;
    }
    protected function sv(string $v): string
    {
        return sprintf(self::SAFE_CHAR . "%s" . self::SAFE_CHAR, $v);
    }
    public function select($expression): PdoExtQueryBuilderInterface
    {
        if (is_string($expression)) {
            $this->select = explode(",", $expression);
        } elseif(is_array($expression)) {
            $this->select = $expression;
        }
        return $this;
    }
    public function from($tableReferences): PdoExtQueryBuilderInterface
    {
        if (is_string($tableReferences)) {
            $this->from = explode(",", $tableReferences);
        } elseif(is_array($tableReferences)) {
            $this->from = $tableReferences;
        } elseif($tableReferences instanceof \Closure) {
            $this->from = (array) $tableReferences();
        }
        return $this;
    }
    protected function whereAdd($condition, string $operand = ''): void
    {
        $where = [];
        if (is_string($condition)) {
            $where = explode(",", $condition);
        } elseif(is_array($condition)) {
            $where = $condition;
        } elseif($condition instanceof \Closure) {
            $where = (array) $condition();
        }
        $subOperand = '';
        $subWhere = [];
        foreach($where as $k => $v) {
            if (!is_int($k)) {
                if (!empty($v)) {
                    $subWhere[] = self::TAB . sprintf('%s%s = ?', $subOperand, $k);
                    if ($v instanceof \Closure) {
                        $v = $v();
                    }
                    $this->bindings[] = $v;
                    if (empty($subOperand)) {
                        $subOperand = 'and ';
                    }
                }
            } elseif (is_string($v)) {
                $subWhere[] = self::TAB . $v;
            } elseif ($v instanceof \Closure) {
                $subWhere[] = self::TAB . $v();
            }
        }
        if (!empty($subWhere)) {
            if (!empty($operand)) {
                $this->where[] = $operand;
            }
            $this->where[] = '(';
            foreach($subWhere as $w) {
                $this->where[] = $w;
            }
            $this->where[] = ')';
        }
    }
    public function where($condition): PdoExtQueryBuilderInterface
    {
        $this->whereAdd($condition);
        return $this;
    }
    public function andWhere($condition): PdoExtQueryBuilderInterface
    {
        $this->whereAdd($condition, 'and');
        return $this;
    }
    public function orWhere($condition): PdoExtQueryBuilderInterface
    {
        $this->whereAdd($condition, 'or');
        return $this;
    }
    public function groupBy($expression): PdoExtQueryBuilderInterface
    {
        if (is_string($expression)) {
            $this->groupBy = explode(",", $expression);
        } elseif(is_array($expression)) {
            $this->groupBy = $expression;
        } elseif($expression instanceof \Closure) {
            $this->groupBy = (array) $expression();
        }
        return $this;
    }
    public function having($condition): PdoExtQueryBuilderInterface
    {
        if (is_string($condition)) {
            $this->having = explode(",", $condition);
        } elseif(is_array($condition)) {
            $this->having = $condition;
        } elseif($condition instanceof \Closure) {
            $this->having = (array) $condition();
        }
        return $this;
    }
    public function orderBy($expression): PdoExtQueryBuilderInterface
    {
        $orderBy = [];
        if (is_string($expression)) {
            $orderBy = explode(",", $expression);
        } elseif(is_array($expression)) {
            $orderBy = $expression;
        } elseif($expression instanceof \Closure) {
            $orderBy = (array) $expression();
        }
        foreach($orderBy as $k => $v) {
            if (!is_int($k)) {
                if (!empty($k) && in_array(strtolower($v), self::ORDER_BY_LIST)) {
                    $this->orderBy[] = trim($k . ' ' . $v);
                }
            } else {
                if (!empty($v) && is_string($v)) {
                    $this->orderBy[] = $v;
                }
            }
        }
        return $this;
    }
    public function offset(int $offset): PdoExtQueryBuilderInterface
    {
        $this->offset = $offset;
        if (is_null($this->limit)) {
            $this->limit = 1;
        }
        return $this;
    }
    public function limit(int $limit): PdoExtQueryBuilderInterface
    {
        $this->limit = $limit;
        return $this;
    }
    public function getSql(): string
    {
        $sql = [];

        $sql[] = "select" . PHP_EOL . self::TAB . implode("," . PHP_EOL. self::TAB , $this->select) . PHP_EOL;

        $sql[] = "from" . PHP_EOL . self::TAB . implode("," . PHP_EOL. self::TAB , $this->from) . PHP_EOL;

        if (!empty($this->where)) {
            $sql[] = "where" . PHP_EOL . self::TAB . implode(PHP_EOL. self::TAB, $this->where) . PHP_EOL;
        }

        if (!empty($this->orderBy)) {
            $sql[] = "order by" . PHP_EOL . self::TAB . implode("," . PHP_EOL. self::TAB , $this->orderBy) . PHP_EOL;
        }

        //limit
        if (!is_null($this->offset) && !is_null($this->limit)) {
            $sql[] = strtr('limit :offset, :limit', [':offset' => $this->offset, ':limit' => $this->limit]);
        } elseif (!is_null($this->limit)) {
            $sql[] = sprintf('limit %d', $this->limit);
        }
        return str_replace(
            [
                self::TAB
            ],
            [
                str_pad(self::SPACE, self::TAB_LEN)
            ],
            implode("", $sql)
        );
    }
    public function getBindings(): array
    {
        return $this->bindings;
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