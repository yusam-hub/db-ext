<?php

namespace YusamHub\DbExt\Tests;

use PHPUnit\Framework\TestCase;
use YusamHub\DbExt\PdoExt;

class BaseTestCase extends TestCase
{
    static public ?PdoExt $pdoExt = null;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        if (is_null(self::$pdoExt)) {
            self::$pdoExt = db_ext_mysql_pdo_ext_create_from_config(include __DIR__ . "/../config/config.php");
            //self::$pdoExt->isDebugging = true;
        }
    }

}