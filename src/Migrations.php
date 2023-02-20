<?php

namespace YusamHub\DbExt;

abstract class Migrations
{
    protected string $migrationDir;
    protected string $migrationFile;

    /**
     * @param string $migrationDir
     */
    public function __construct(string $migrationDir)
    {
        $this->migrationDir = realpath($migrationDir);
        $this->migrationFile = $this->migrationDir . DIRECTORY_SEPARATOR . "migrations.txt";
    }

    /**
     * @param string $sql
     * @return void
     */
    abstract protected function query(string $sql): void;

    /**
     * @return void
     */
    public function migrate(): void
    {
        $files = $this->getMigrationFiles();
        if (empty($files)) {
            echo PHP_EOL;
            echo sprintf('%s', 'EMPTY MIGRATION FILES') . PHP_EOL;
        } else {
            $scriptNumber = 0;
            foreach($files as $file) {
                $content = "";
                if (str_ends_with($file, '.sql')) {
                    $content = file_get_contents($file);
                } elseif (str_ends_with($file, '.php')) {
                    $obj = include $file;
                    if (method_exists( $obj, 'getQuery')) {
                        $content = $obj->getQuery();
                    }
                }
                $content .= "\r";
                $queries = explode(";\r", $content);
                foreach($queries as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        $scriptNumber++;
                        try {
                            $this->query($query);
                            echo PHP_EOL;
                            echo sprintf('%s. %s - OK',  str_pad($scriptNumber, 8, '0', STR_PAD_LEFT), $query) . PHP_EOL;

                        } catch (\Throwable $e) {
                            echo sprintf('%s. %s - FAIL in file %s', str_pad($scriptNumber, 8, '0', STR_PAD_LEFT), $query, basename($file)) . PHP_EOL;
                            return;
                        }
                    }
                }
                file_put_contents($this->migrationFile, basename($file) . PHP_EOL, FILE_APPEND);
            }
        }
        echo sprintf('%s', 'MIGRATION SUCCESS') . PHP_EOL;
    }

    protected function getMigrationFiles(): array
    {
        $allFiles = array_merge(
            glob($this->migrationDir . DIRECTORY_SEPARATOR . '*.sql'),
            glob($this->migrationDir . DIRECTORY_SEPARATOR . '*.php')
        );

        $migrationFiles = [];
        if (file_exists($this->migrationFile)) {
            $migrationFiles = array_filter(explode(PHP_EOL, file_get_contents($this->migrationFile)));
            $migrationFiles = array_map(function($v){
                return $this->migrationDir . DIRECTORY_SEPARATOR . $v;
            }, $migrationFiles);
        }
        return array_diff($allFiles, $migrationFiles);
    }

}