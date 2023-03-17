<?php

namespace YusamHub\DbExt\Traits;

use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

trait GetSetPdoExtKernelTrait
{
    protected ?PdoExtKernelInterface $pdoExtKernel = null;

    public function hasPdoExtKernel(): bool
    {
        return !is_null($this->pdoExtKernel);
    }
    public function getPdoExtKernel(): ?PdoExtKernelInterface
    {
        return $this->pdoExtKernel;
    }
    public function setPdoExtKernel(?PdoExtKernelInterface $pdoExtKernel): void
    {
        $this->pdoExtKernel = $pdoExtKernel;
    }
}