<?php

namespace YusamHub\DbExt\Interfaces;

interface PdoExtKernelInterface
{
    function pdoExt(?string $connectionName = null): PdoExtInterface;
    function createPdoExt(string $connectionName): PdoExtInterface;
    function getDefaultConnectionName(): string;
    function getConnectionNames(): array;
}