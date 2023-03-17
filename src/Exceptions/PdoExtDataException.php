<?php

namespace YusamHub\DbExt\Exceptions;
class PdoExtDataException extends \RuntimeException
{
    protected array $data;
    
    public function __construct(array $data = [], $message = "", $code = 0, \Throwable $previous = null)
    {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }

    public function getData(): array
    {
        return $this->data;
    }
}