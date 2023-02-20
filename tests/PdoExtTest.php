<?php

namespace YusamHub\DbExt\Tests;

use YusamHub\DbExt\PdoExtMigrations;

class PdoExtTest extends BaseTestCase
{
    public function testConnection()
    {
        $this->assertTrue(self::$pdoExt->isMySqlDateTime(self::$pdoExt->selectMySqlDateTime()));
    }

    public function testSql()
    {
        $id = self::$pdoExt->insertReturnId('test', [
            'title' => 'title',
            'desc' => null,
        ]);
        $this->assertTrue($id > 0);

        $rows = self::$pdoExt->fetchAll("SELECT * FROM test");
        $this->assertTrue(isset($rows[0]['id']));

        $row = self::$pdoExt->fetchOne("SELECT * FROM test");
        $this->assertTrue(isset($row['id']));

        $id = self::$pdoExt->fetchOneColumn("SELECT * FROM test",'id');
        $this->assertTrue(!is_null($id));

        $res = self::$pdoExt->update('test', [
            'desc' => 'test',
        ], [
            'id' => $id,
        ], 1);
        $this->assertTrue($res && self::$pdoExt->affectedRows() === 1);

        $res = self::$pdoExt->delete('test', [
            'id' => $id,
        ], 1);
        $this->assertTrue($res > 0 && self::$pdoExt->affectedRows() === 1);
    }

    public function testEscape()
    {
        $value = self::$pdoExt->escape("'test");

        $str = self::$pdoExt->fetchOneColumn(strtr("SELECT ':str' as str", [
            ':str' => $value
        ]),'str');

        var_dump($value, $str);

        $this->assertTrue($str === "'test");
    }
}