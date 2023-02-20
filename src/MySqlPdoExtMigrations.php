<?php

namespace YusamHub\DbExt;

class MySqlPdoExtMigrations extends Migrations
{
    protected MySqlPdoExt $pdoExt;

    /**
     * @param MySqlPdoExt $pdoExt
     * @param string $migrationDir
     */
    public function __construct(MySqlPdoExt $pdoExt, string $migrationDir)
    {
        $this->pdoExt = $pdoExt;
        parent::__construct($migrationDir);
    }

    /**
     * @param string $sql
     * @return void
     */
    protected function query(string $sql): void
    {
        $this->pdoExt->exec($sql);
    }
}