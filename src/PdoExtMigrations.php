<?php

namespace YusamHub\DbExt;

class PdoExtMigrations extends Migrations
{
    protected PdoExt $pdoExt;

    /**
     * @param PdoExt $pdoExt
     * @param string $migrationDir
     * @param string $storageFile
     */
    public function __construct(PdoExt $pdoExt, string $migrationDir, string $storageFile)
    {
        $this->pdoExt = $pdoExt;
        parent::__construct($migrationDir, $storageFile);
    }

    /**
     * @param string $sql
     * @return void
     */
    protected function query(string $sql): void
    {
        $this->pdoExt->exec($sql);
    }

    protected function beginTransaction(): void
    {
        $this->pdoExt->beginTransaction();
    }

    protected function commitTransaction(): void
    {
        $this->pdoExt->commitTransaction();
    }

    protected function rollBackTransaction(): void
    {
        $this->pdoExt->rollBackTransaction();
    }
}