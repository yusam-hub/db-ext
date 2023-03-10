<?php

namespace YusamHub\DbExt;

class MySqlSplitter
{
    /**
     * @param string $sql
     * @param string $delimiter
     * @return array
     */
    public static function split(string $sql, string $delimiter = ';'): array
    {
        $queries = [];
        $restOfQuery = null;
        while (true) {
            if ($restOfQuery == null) {
                $restOfQuery = $sql;
            }
            $statementAndRest = self::getStatements($restOfQuery, $delimiter);
            $statement = $statementAndRest[0];
            if ($statement != null && trim($statement) != "") {
                $queries[] = $statement;
            }
            $restOfQuery = $statementAndRest[1];
            if ($restOfQuery == null || trim($restOfQuery) == "") {
                break;
            }
        }

        return $queries;
    }

    /**
     * @param string $query
     * @param string $delimiter
     * @return string[]
     */
    private static function getStatements(string $query, string $delimiter): array
    {
        $charArray = self::toArray($query);
        $previousChar = null;
        $nextChar = null;
        $isInComment = false;
        $commentChar = null;
        $isInString = false;
        $stringChar = null;
        $isInTag = false;
        $tagChar = null;

        for ($index = 0; $index < count($charArray); $index++) {
            $char = $charArray[$index];
            $previousChar = $index > 0 ? $charArray[$index - 1] : null;
            $nextChar = $index < count($charArray) - 1 ? $charArray[$index + 1] : null;
            // it's in string, go to next char
            if ($previousChar != '\\' && ($char == '\'' || $char == '"') && !$isInString && !$isInComment) {
                $isInString = true;
                $stringChar = $char;
                continue;
            }
            // it's comment, go to next char
            if ((($char == '#' && $nextChar == ' ') || ($char == '-' && $nextChar == '-') || ($char == '/' && $nextChar == '*')) && !$isInString) {
                $isInComment = true;
                $commentChar = $char;
                continue;
            }
            // it's end of comment, go to next
            if ($isInComment && ((($commentChar == '#' || $commentChar == '-') && $char == "\n") || ($commentChar == '/' && ($char == '*' && $nextChar == '/')))) {
                $isInComment = false;
                $commentChar = null;
                continue;
            }
            // string closed, go to next char
            if ($previousChar != '\\' && $char == $stringChar && $isInString) {
                $isInString = false;
                $stringChar = null;
                continue;
            }
            if (strtolower($char) == 'd' && !$isInComment && !$isInString) {
                $delimiterResult = self::getDelimiter($index, $query);
                if ($delimiterResult != null) {
                    // it's delimiter
                    list($delimiterSymbol, $delimiterEndIndex) = $delimiterResult;
                    $query = substr($query, $delimiterEndIndex);
                    return self::getStatements($query, $delimiterSymbol);
                }
            }
            if (strlen($delimiter) > 1 && array_key_exists($index + strlen($delimiter) - 1, $charArray)) {
                for ($i = $index + 1; $i < $index + strlen($delimiter); $i++) {
                    $char .= $charArray[$i];
                }
            }

            if (strtolower($char) == strtolower($delimiter) && !$isInString && !$isInComment) {
                $splittingIndex = $index;
                return self::getQueryParts($query, $splittingIndex, $delimiter);
            }
        }

        if ($query != null) {
            $query = trim($query);
        }

        return [$query, null];
    }

    /**
     * @param string $query
     * @param int $splittingIndex
     * @param string $delimiter
     * @return array
     */
    private static function getQueryParts(string $query, int $splittingIndex, string $delimiter): array
    {
        $statement = substr($query, 0, $splittingIndex);
        $restOfQuery = substr($query, $splittingIndex + strlen($delimiter));
        if ($statement != null) {
            $statement = trim($statement);
        }
        return [$statement, $restOfQuery];
    }

    /**
     * @param int $index
     * @param string $query
     * @return array|null
     */
    private static function getDelimiter(int $index, string $query): ?array
    {
        $delimiterKeyword = 'delimiter ';
        $delimiterLength = strlen($delimiterKeyword);
        $parsedQueryAfterIndexOriginal = substr($query, $index);
        $indexOfDelimiterKeyword = strpos(strtolower($parsedQueryAfterIndexOriginal), $delimiterKeyword);
        if ($indexOfDelimiterKeyword === 0) {
            $parsedQueryAfterIndex = substr($query, $index);
            $indexOfNewLine = strpos($parsedQueryAfterIndex, "\n");
            if ($indexOfNewLine == -1) {
                $indexOfNewLine = strlen($query);
            }
            $parsedQueryAfterIndex = substr($parsedQueryAfterIndex, 0, $indexOfNewLine);
            $parsedQueryAfterIndex = substr($parsedQueryAfterIndex, $delimiterLength);
            $delimiterSymbol = trim($parsedQueryAfterIndex);
            $delimiterSymbol = self::clearTextUntilComment($delimiterSymbol);
            if ($delimiterSymbol != null) {
                $delimiterSymbol = trim($delimiterSymbol);
                $delimiterSymbolEndIndex = strpos($parsedQueryAfterIndexOriginal,
                        $delimiterSymbol) + $index + strlen($delimiterSymbol);

                return [$delimiterSymbol, $delimiterSymbolEndIndex];
            }
        }

        return null;
    }

    /**
     * @param string $text
     * @return string
     */
    private static function clearTextUntilComment(string $text): ?string
    {
        $previousChar = null;
        $nextChar = null;
        $charArray = self::toArray($text);
        $clearedText = null;
        for ($index = 0; $index < count($charArray); $index++) {
            $char = $charArray[$index];

            $nextChar = $index < count($charArray) - 1
                ? $charArray[$index + 1]
                : null;

            if ((($char == '#' && $nextChar == ' ') || ($char == '-' && $nextChar == '-') || ($char == '/' && $nextChar == '*'))) {
                break;
            } else {
                if ($clearedText == null) {
                    $clearedText = '';
                }
                $clearedText .= $char;
            }
        }

        return $clearedText;
    }

    /**
     * @param string $query
     * @return array
     */
    private static function toArray(string $query): array
    {
        return preg_split('//u', $query, -1, PREG_SPLIT_NO_EMPTY);
    }
}