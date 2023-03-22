<?php

namespace YusamHub\DbExt;

use YusamHub\DbExt\Interfaces\PdoExtQueryBuilderInterface;

class PdoExtQueryBuilder implements PdoExtQueryBuilderInterface
{
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
    protected function fetchOperandFromValue(string $value, &$trimmedValue): string
    {
        foreach(self::OPERAND_LIST as $operand) {
            if (str_starts_with(strtolower($value), $operand.":")) {
                $trimmedValue = ltrim($value, $operand.":");
                return $operand;
            }
        }
        $trimmedValue = $value;
        return '=';
    }

    protected function makeLikeFromValue(string $value): string
    {

        return $value;
    }

    protected function whereAdd($condition, string $groupCondition = ''): void
    {
        $where = [];
        if (is_string($condition)) {
            $where = explode(",", $condition);
        } elseif(is_array($condition)) {
            $where = $condition;
        } elseif($condition instanceof \Closure) {
            $where = (array) $condition();
        }
        $subCondition = '';
        $subWhere = [];
        foreach($where as $k => $v) {
            if (!is_int($k)) {
                if (!empty($v)) {
                    if ($v instanceof \Closure) {
                        $v = $v();
                    }
                    $operand = $this->fetchOperandFromValue($v, $v);
                    if ($operand === self::OPERAND_BETWEEN) {
                        $between = explode(",", $v);
                        if (isset($between[0], $between[1])) {
                            $subWhere[] = self::TAB . sprintf('%s%s between ? and ?', $subCondition, $k);
                            $this->bindings[] = $between[0];
                            $this->bindings[] = $between[1];
                        }
                    } elseif ($operand === self::OPERAND_IN) {
                        $in = array_filter(explode(",", $v));
                        $c = count($in);
                        if ($c > 0 and $c <= 100) {
                            $inString = [];
                            for($i = 0; $i < $c; $i++) {
                                $inString[] = '?';
                                $this->bindings[] = $in[$i];
                            }
                            $subWhere[] = self::TAB . sprintf('%s%s in (%s)', $subCondition, $k, implode(",", $inString));
                        }
                    }  elseif ($operand === self::OPERAND_LIKE_FIRST || $operand === self::OPERAND_LIKE_END || $operand === self::OPERAND_LIKE_CONTAINS) {

                        if ($operand === self::OPERAND_LIKE_FIRST) {
                            $subWhere[] = self::TAB . sprintf("%s%s like %s", $subCondition, $k, "CONCAT(?,'%')");
                            $this->bindings[] = $v;
                        } elseif ($operand === self::OPERAND_LIKE_END) {
                            $subWhere[] = self::TAB . sprintf("%s%s like %s", $subCondition, $k, "CONCAT('%',?)");
                            $this->bindings[] = $v;
                        } elseif ($operand === self::OPERAND_LIKE_CONTAINS) {
                            $subWhere[] = self::TAB . sprintf("%s%s like %s", $subCondition, $k, "CONCAT('%',?,'%')");
                            $this->bindings[] = $v;
                        }

                    } else {
                        $subWhere[] = self::TAB . sprintf('%s%s %s ?', $subCondition, $k, $operand);
                        $this->bindings[] = $v;
                    }
                    if (empty($subCondition)) {
                        $subCondition = 'and ';
                    }
                }
            } else {
                if (is_string($v)) {
                    $subWhere[] = self::TAB . $v;
                } elseif ($v instanceof \Closure) {
                    $subWhere[] = self::TAB . $v();
                }
            }
        }
        if (!empty($subWhere)) {
            if (!empty($groupCondition)) {
                $this->where[] = $groupCondition;
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