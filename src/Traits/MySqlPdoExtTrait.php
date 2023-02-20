<?php

namespace YusamHub\DbExt\Traits;

trait MySqlPdoExtTrait
{
    /**
     * @param string $name
     * @param int $timeout
     */
    public function mySqlLockBegin(string $name, int $timeout = 10): void
    {
        $this->exec("SELECT GET_LOCK('" . md5($name) . "'," . $timeout . ")");
    }

    /**
     * @param string $name
     */
    public function mySqlLockEnd(string $name): void
    {
        $this->exec("SELECT RELEASE_LOCK('" .  md5($name) . "')");
    }

    /**
     * @param string $name
     * @return bool
     */
    public function mySqlIsLocked(string $name): bool
    {
        $isFreeLock = $this->fetchOneColumn("SELECT IS_FREE_LOCK('" .  md5($name) . "') as isFreeLock", 'isFreeLock');
        return !is_null($isFreeLock) && !intval($isFreeLock);
    }

    /**
     * @return string
     */
    public function selectMySqlDateTime(): string
    {
        return $this->fetchOneColumn("SELECT NOW() AS dt", 'dt');
    }

    /**
     * @param string $value
     * @return bool
     */
    public function isMySqlDateTime(string $value): bool
    {
        return preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/', $value);
    }

    /**
     * @param string $value
     * @return bool
     */
    public function isMySqlDate(string $value): bool
    {
        return preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $value);
    }
}