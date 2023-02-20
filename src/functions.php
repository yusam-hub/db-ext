<?php

if (! function_exists('db_ext_mysql_pdo_ext_create')) {

    function db_ext_mysql_pdo_ext_create(string $host, string $port, string $user, string $password, string $dbName = null, bool $newConnection = false): \YusamHub\DbExt\MySqlPdoExt
    {
        $options = [];
        if (!$newConnection) {
            $options[\PDO::ATTR_PERSISTENT] = true;
        }

        return new \YusamHub\DbExt\MySqlPdoExt(new \PDO(
            sprintf('mysql:host=%s;dbname=%s',$host . ':' . $port,$dbName),
            $user,
            $password,
            $options
        ));
    }

}

if (! function_exists('db_ext_mysql_pdo_ext_create_from_config')) {

    function db_ext_mysql_pdo_ext_create_from_config(array $config, bool $newConnection = false): \YusamHub\DbExt\MySqlPdoExt
    {
        return db_ext_mysql_pdo_ext_create($config['host']??'',$config['port']??'',$config['user']??'',$config['password']??'',$config['dbName']??'', $newConnection);
    }

}
