<?php

namespace YusamHub\DbExt;

class ExceptionPdoExt extends \Exception
{
    protected string $lastSql;
    protected array $lastBindings;
    protected int $affectedRows;
    protected ?int $lastInsertId;

    /**
     * @param PdoExt $pdoExt
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(PdoExt $pdoExt, string $message = "", int $code = 0, \Throwable $previous = null)
    {
        $this->lastSql = $pdoExt->getLastSql();
        $this->lastBindings = $pdoExt->getLastBindings();
        $this->affectedRows = $pdoExt->affectedRows();
        $this->lastInsertId = $pdoExt->lastInsertId();
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getLastSql(): string
    {
        return $this->lastSql;
    }

    /**
     * @return array
     */
    public function getLastBindings(): array
    {
        return $this->lastBindings;
    }

    /**
     * @return int
     */
    public function affectedRows(): int
    {
        return $this->affectedRows;
    }

    /**
     * @return int|null
     */
    public function lastInsertId(): ?int
    {
        return $this->lastInsertId;
    }

    public function getData(): array
    {
        return [
            'lastSql' => $this->lastSql,
            'lastBindings' => $this->lastBindings,
            'affectedRows' => $this->affectedRows,
            'lastInsertId' => $this->lastInsertId
        ];
    }
}