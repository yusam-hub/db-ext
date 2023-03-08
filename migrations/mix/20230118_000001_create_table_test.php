<?php

return new class {
    public function getQuery(): string
    {
        return <<<MYSQL
DROP TABLE IF EXISTS `test`;
MYSQL;
    }

};