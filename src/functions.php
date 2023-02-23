<?php

if (! function_exists('db_ext_mysql_pdo_ext_create')) {

    function db_ext_mysql_pdo_ext_create(string $host, string $port, string $user, string $password, string $dbName = null, bool $newConnection = false): \YusamHub\DbExt\PdoExt
    {
        $options = [];
        if (!$newConnection) {
            $options[\PDO::ATTR_PERSISTENT] = true;
        }

        return new \YusamHub\DbExt\PdoExt(new \PDO(
            sprintf('mysql:host=%s;dbname=%s',$host . ':' . $port,$dbName),
            $user,
            $password,
            $options
        ));
    }

}

if (! function_exists('db_ext_mysql_pdo_ext_create_from_config')) {

    function db_ext_mysql_pdo_ext_create_from_config(array $config, bool $newConnection = false): \YusamHub\DbExt\PdoExt
    {
        return db_ext_mysql_pdo_ext_create($config['host']??'',$config['port']??'',$config['user']??'',$config['password']??'',$config['dbName']??'', $newConnection);
    }

}

if (! function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle): bool
    {
        $length = strlen( $needle );
        return substr( $haystack, 0, $length ) === $needle;
    }
}

if (! function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle): bool
    {
        $length = strlen( $needle );
        if( !$length ) {
            return true;
        }
        return substr( $haystack, -$length ) === $needle;
    }
}
