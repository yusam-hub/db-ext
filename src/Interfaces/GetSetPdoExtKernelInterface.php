<?php

namespace YusamHub\DbExt\Interfaces;

interface GetSetPdoExtKernelInterface
{
    public function hasPdoExtKernel(): bool;
    public function getPdoExtKernel(): ?PdoExtKernelInterface;
    public function setPdoExtKernel(?PdoExtKernelInterface $pdoExtKernel): void;
}