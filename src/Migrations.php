<?php

namespace YusamHub\DbExt;

abstract class Migrations
{
    protected string $migrationDir;
    protected string $storageFile;

    protected ?\Closure $echoLineClosure = null;

    /**
     * @param string $migrationDir
     * @param string $storageFile
     */
    public function __construct(string $migrationDir, string $storageFile)
    {
        $this->migrationDir = realpath($migrationDir);
        $this->storageFile = $storageFile;
    }

    /**
     * @param \Closure $closure
     * @return void
     */
    public function setEchoLineClosure(\Closure $closure): void
    {
        $this->echoLineClosure = $closure;
    }

    /**
     * @param string $level
     * @param string $message
     * @return void
     */
    protected function echoLine(string $level = 'INFO', string $message = ''): void
    {
        if (is_callable($this->echoLineClosure)) {
            $closure = $this->echoLineClosure;
            $closure($level, $message);
            return;
        }
        echo $message . PHP_EOL;
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
            return;
        }

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
            $content = trim($content);
            $content = rtrim($content, ';');
            $content .= ';\r';
            $queries = explode(";\r", $content);
            foreach($queries as $query) {
                $query = trim($query);
                $query = rtrim($query, ';\r');
                if (!empty($query)) {
                    $scriptNumber++;
                    $this->echoLine("INFO", sprintf('%s. %s',  str_pad($scriptNumber, 8, '0', STR_PAD_LEFT), $query));
                    try {
                        $this->query($query);
                        $this->echoLine();
                    } catch (\Throwable $e) {
                        $this->echoLine("ERROR", sprintf('%s. FAIL (%s) in file %s', str_pad($scriptNumber, 8, '0', STR_PAD_LEFT), $e->getMessage(), basename($file)));
                        $this->echoLine();
                        return;
                    }
                }
            }
            file_put_contents($this->storageFile, basename($file) . PHP_EOL, FILE_APPEND);
        }
    }

    protected function getMigrationFiles(): array
    {
        $allFiles = array_merge(
            glob($this->migrationDir . DIRECTORY_SEPARATOR . '*.sql'),
            glob($this->migrationDir . DIRECTORY_SEPARATOR . '*.php')
        );

        sort($allFiles);

        $migrationFiles = [];
        if (file_exists($this->storageFile)) {
            $migrationFiles = array_filter(explode(PHP_EOL, file_get_contents($this->storageFile)));
            $migrationFiles = array_map(function($v){
                return $this->migrationDir . DIRECTORY_SEPARATOR . $v;
            }, $migrationFiles);
        }
        return array_diff($allFiles, $migrationFiles);
    }

}