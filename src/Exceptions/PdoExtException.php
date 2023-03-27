<?php

namespace YusamHub\DbExt\Exceptions;

use YusamHub\DbExt\Interfaces\PdoExtExceptionInterface;
use YusamHub\DbExt\Interfaces\PdoExtInterface;

class PdoExtException extends \RuntimeException implements PdoExtExceptionInterface
{
    protected string $lastSql;
    protected array $lastBindings;
    protected int $affectedRows;
    protected ?int $lastInsertId;

    protected array $pdoExtra;

    /**
     * @param PdoExtInterface $pdoExt
     * @param string $message
     * @param $code
     * @param \Throwable|null $previous
     */
    public function __construct(PdoExtInterface $pdoExt, string $message = "", $code = 0, ?\Throwable $previous = null)
    {
        $this->lastSql = $pdoExt->getLastSql();
        $this->lastBindings = $pdoExt->getLastBindings();
        $this->affectedRows = $pdoExt->affectedRows();
        $this->lastInsertId = $pdoExt->lastInsertId();
        $this->pdoExtra = [
            'code' => $code,
            'errorInfo' => $pdoExt->getPdo()->errorInfo()
        ];
        parent::__construct($message, 0, $previous);
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

    public function getPdoExtra(): array
    {
        return $this->pdoExtra;
    }

    public function getData(): array
    {
        return [
            'lastSql' => $this->getLastSql(),
            'lastBindings' => $this->getLastBindings(),
            'affectedRows' => $this->getAffectedRows(),
            'lastInsertId' => $this->getLastInsertId(),
            'pdoExtra' => $this->getPdoExtra(),
        ];
    }
}