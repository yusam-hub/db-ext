<?php

namespace YusamHub\DbExt;

use YusamHub\DbExt\Interfaces\PdoExtInterface;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

abstract class PdoExtKernel implements PdoExtKernelInterface
{
    protected static ?PdoExtKernel $instance = null;
    protected array $pdoExtConnections = [];

    /**
     * @param string|null $connectionName
     * @return PdoExtInterface
     */
    public function pdoExt(?string $connectionName = null): PdoExtInterface
    {
        if (is_null($connectionName)) {
            $connectionName = $this->getDefaultConnectionName();
        }

        if (isset($this->pdoExtConnections[$connectionName])) {
            return $this->pdoExtConnections[$connectionName];
        }

        return $this->pdoExtConnections[$connectionName] = $this->createPdoExt($connectionName);
    }

    public function pdoExtClose(?string $connectionName = null): void
    {
        if (is_null($connectionName)) {
            $connectionName = $this->getDefaultConnectionName();
        }

        if (isset($this->pdoExtConnections[$connectionName])) {
            unset($this->pdoExtConnections[$connectionName]);
        }
    }

    abstract public function createPdoExt(string $connectionName): PdoExtInterface;

    /**
     * @return string
     */
    abstract public function getDefaultConnectionName(): string;

    /**
     * @return array
     */
    abstract public function getConnectionNames(): array;

}