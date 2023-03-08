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
     * @param string $message
     * @return void
     */
    protected function echoLine(string $message = ''): void
    {
        if (is_callable($this->echoLineClosure)) {
            $closure = $this->echoLineClosure;
            $closure($message);
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
            $this->echoLine();
            $this->echoLine(sprintf('%s', 'EMPTY MIGRATION FILES'));
        } else {
            $scriptNumber = 0;
            foreach($files as $file) {
                $this->echoLine(sprintf("Try file [%s]", $file));
                $this->echoLine();
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
                            $this->echoLine();
                            $this->echoLine(sprintf('%s. %s - OK',  str_pad($scriptNumber, 8, '0', STR_PAD_LEFT), $query));
                            $this->echoLine();
                        } catch (\Throwable $e) {
                            $this->echoLine(sprintf('%s. %s - FAIL in file %s', str_pad($scriptNumber, 8, '0', STR_PAD_LEFT), $query, basename($file)));
                            $this->echoLine();
                            return;
                        }
                    }
                }
                file_put_contents($this->storageFile, basename($file) . PHP_EOL, FILE_APPEND);
            }
        }
        $this->echoLine(sprintf('%s', 'MIGRATION SUCCESS'));
    }

    protected function getMigrationFiles(): array
    {
        $this->echoLine(sprintf("Migration directory [%s]", $this->migrationDir));
        $this->echoLine();

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