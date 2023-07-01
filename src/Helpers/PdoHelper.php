<?php

namespace YusamHub\DbExt\Helpers;
class PdoHelper
{
    /**
     * @param array $array
     * @return array
     */
    public static function prepareData(array $array): array
    {
        $keys = "";
        $values = array_values($array);
        $placeholder = self::in($array);
        if (sizeof(array_filter($listOfKeys = array_keys($array), fn ($item) => is_string($item))) > 0) {
            $keys = self::backTicksList($listOfKeys);
        }
        return [$keys, $values, $placeholder];
    }

    /**
     * @param array $array
     * @return array
     */
    public static function prepareBatchInsertData(array $array): array
    {
        $keys = "";
        $values = [];
        $placeholder = "";
        if (sizeof($array) > 0) {
            $listOfKeys = array_keys(reset($array));
            if (sizeof(array_filter($listOfKeys, fn ($item) => is_string($item))) > 0) {
                $keys = self::backTicksList($listOfKeys);
            }
            foreach ($array as $item) {
                array_push($values, ...array_values($item));
                $placeholder .= ",(" . self::in($item) . ")";
            }
        }
        return [$keys, $values, ltrim($placeholder, ',')];
    }

    /**
     * @param array $array
     * @return string
     */
    public static function in(array $array): string
    {
        return str_repeat("?,", count($array) - 1) . "?";
    }

    /**
     * @param array $array
     * @return string
     */
    public static function backTicksList(array $array): string
    {
        return "`" . implode("`,`", $array) . "`";
    }

    /**
     * @param \Exception $e
     * @return bool
     */
    public static function checkPDOError1927(\Exception $e): bool
    {
        return $e instanceof \PDOException && isset($e->errorInfo[1]) && $e->errorInfo[1] === 1927;
    }

    /**
     * @param \Exception $e
     * @return bool
     */
    public static function checkPDOGoneAway2006(\Exception $e): bool
    {
        return $e instanceof \PDOException && isset($e->errorInfo[1]) && $e->errorInfo[1] === 2006;
    }

    /**
     * @param \Exception $e
     * @return bool
     */
    public static function checkPDOGoneAway1062(\Exception $e): bool
    {
        return $e instanceof \PDOException && isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062;
    }
}