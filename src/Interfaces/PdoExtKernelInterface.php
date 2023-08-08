<?php

namespace YusamHub\DbExt\Interfaces;

interface PdoExtKernelInterface
{
    function pdoExt(?string $connectionName = null): PdoExtInterface;
    function pdoExtClose(?string $connectionName = null): void;
    function createPdoExt(string $connectionName): PdoExtInterface;
    function getDefaultConnectionName(): string;
    function getConnectionNames(): array;
}