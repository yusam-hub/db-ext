<?php

namespace YusamHub\DbExt\Tests;

use PHPUnit\Framework\TestCase;
use YusamHub\DbExt\MySqlPdoExtMigrations;

class PdoExtTest extends TestCase
{
    public function testConnection()
    {
        $pdoExt = db_ext_mysql_pdo_ext_create_from_config(include __DIR__ . "/../config/config.php");
        $this->assertTrue($pdoExt->isDateTime($pdoExt->selectDateTime()));

        $mySqlPdoExtMigrations = new MySqlPdoExtMigrations($pdoExt, __DIR__ . '/../migrations/php');
        $mySqlPdoExtMigrations->migrate();

        $mySqlPdoExtMigrations = new MySqlPdoExtMigrations($pdoExt, __DIR__ . '/../migrations/sql');
        $mySqlPdoExtMigrations->migrate();
    }

}