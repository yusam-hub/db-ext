<?php

namespace YusamHub\DbExt\Tests;

use YusamHub\DbExt\PdoExt;

class PdoExtTest extends BaseTestCase
{

    public function testDefault()
    {
        $queryBuilder = self::$pdoExt->queryBuilder();
        $queryBuilder->select(['t1.col1','t2.col2 as c2']);
        $queryBuilder->from(['table1 t1','table2 t2']);
        $queryBuilder->where([
            'key1' => 'value1',
            'key2' => function(){
                return 'value2';
            },
        ]);
        /*$queryBuilder->where([
            't1.id = 1',
            'and t2.id = 2',
        ]);*/
        $queryBuilder->andWhere([
            't3 = t4',
            'and t3 = t4',
        ]);
        $queryBuilder->orWhere([
            't5 = t6',
            'and t3 = t4',
        ]);
        $queryBuilder->orderBy(['col1 asc', 't2.col2 desc']);
        $queryBuilder->offset(0);
        $queryBuilder->limit(10);

        print_r($queryBuilder->getSql());
        var_dump($queryBuilder->getBindings());

        $this->assertTrue(true);
    }

    /*public function testConnection()
    {
        $this->assertTrue(self::$pdoExt->isMySqlDateTime(self::$pdoExt->selectMySqlDateTime()));
    }*/

    /*public function testSql()
    {
        self::$pdoExt->onDebugLogCallback(function(string $sql, array $bindings){
            echo "onDebugLogCallback: " . $sql . " ".  json_encode($bindings) . PHP_EOL;
        });
        $id = self::$pdoExt->insertReturnId('test', [
            'title' => 'title',
            'desc' => null,
        ]);
        $this->assertTrue(!is_null($id) && $id > 0);

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
    }*/

    /*public function testEscape()
    {
        $value = self::$pdoExt->escape("'test");

        $str = self::$pdoExt->fetchOneColumn(strtr("SELECT ':str' as str", [
            ':str' => $value
        ]),'str');

        $this->assertTrue($str === "'test");
    }*/
}