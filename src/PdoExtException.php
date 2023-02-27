<?php

namespace YusamHub\DbExt;

class PdoExtException extends \RuntimeException
{
    protected string $lastSql;
    protected array $lastBindings;
    protected int $affectedRows;
    protected ?int $lastInsertId;

    protected array $pdoErrorInfo;

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
        $this->pdoErrorInfo = $pdoExt->getPdo()->errorInfo();
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
    public function getAffectedRows(): int
    {
        return $this->affectedRows;
    }

    /**
     * @return int|null
     */
    public function getLastInsertId(): ?int
    {
        return $this->lastInsertId;
    }

    public function getPdoErrorInfo(): array
    {
        return $this->pdoErrorInfo;
    }

    public function getData(): array
    {
        return [
            'lastSql' => $this->getLastSql(),
            'lastBindings' => $this->getLastBindings(),
            'affectedRows' => $this->getAffectedRows(),
            'lastInsertId' => $this->getLastInsertId(),
            'pdoErrorInfo' => $this->getPdoErrorInfo(),
        ];
    }
}