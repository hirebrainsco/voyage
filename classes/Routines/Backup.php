<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Core\Configuration;
use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Core\Migration;
use Voyage\Core\TableData;
use Voyage\MigrationActions\CreateTableAction;
use Voyage\MigrationActions\InsertRecordAction;

/**
 * Class Backup
 * @package Voyage\Routines
 */
class Backup
{
    /**
     * @var string
     */
    private $exportFilePath = '';

    /**
     * @var EnvironmentControllerInterface
     */
    private $environmentController;

    /**
     * Backup constructor.
     * @param EnvironmentControllerInterface $environmentController
     * @param string $exportFilePath
     */
    public function __construct(EnvironmentControllerInterface $environmentController, $exportFilePath)
    {
        $this->environmentController = $environmentController;
        $this->setExportFilePath($exportFilePath);
    }

    /**
     * @return string
     */
    public function getExportFilePath()
    {
        return $this->exportFilePath;
    }

    /**
     * @param string $exportFilePath
     */
    public function setExportFilePath($exportFilePath)
    {
        $this->exportFilePath = $exportFilePath;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function create()
    {
        if ($this->getExportFilePath() == '') {
            throw new \Exception('Backup filename cannot be empty!');
        }

        $tables = $this->getTables();
        if (empty($tables)) {
            return false;
        }

        $this->dumpTables($tables);
        $this->dumpData($tables);
        return true;
    }

    /**
     * @return array
     */
    protected function getTables()
    {
        $databaseRoutines = new DatabaseRoutines($this->environmentController);
        $tables = $databaseRoutines->getTables();
        $tables[Configuration::getInstance()->getMigrationsTableName()] = new TableData(Configuration::getInstance()->getMigrationsTableName(), false);

        return $tables;
    }

    /**
     * @param array $tables
     */
    protected function dumpData(array $tables)
    {
        $bufferLimit = DataDifference::BufferMaxRecords;
        $code = '';

        /**
         * @var TableData $table
         */
        $records = 0;
        $migration = new Migration($this->environmentController);

        foreach ($tables as $table) {
            if ($table->ignoreData) {
                continue;
            }

            $sql = 'SELECT * FROM `' . $table->name . '`';
            $stmt = $this->environmentController->getDatabaseConnection()->query($sql);

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $records++;
                $action = new InsertRecordAction($this->environmentController->getDatabaseConnection(), $table->name, $row, $migration);
                $code .= DatabaseRoutines::replaceTableNames($action->getApply()) . PHP_EOL;
                unset($action);

                if ($records >= $bufferLimit) {
                    $this->save($code);
                    $records = 0;
                    $code = '';
                }
            }
        }

        if ($records > 0) {
            $this->save($code);
        }
    }

    /**
     * @param array $tables
     * @throws \Exception
     */
    protected function dumpTables(array $tables)
    {
        $code = '';

        /**
         * @var TableData $table
         */
        foreach ($tables as $table) {
            $action = new CreateTableAction($this->environmentController->getDatabaseConnection(), $table->name);
            $code .= DatabaseRoutines::replaceTableNames($action->getRollback() . PHP_EOL . $action->getApply() . PHP_EOL);
            unset($action);
        }

        if ($this->save($code) == false) {
            throw new \Exception('Failed to save dump of tables. Make sure the path "' . Configuration::getInstance()->getPathToBackups() . '" exists and it\'s writable.');
        }
    }

    /**
     * @param $contents
     * @return bool|int
     */
    protected function save($contents)
    {
        return @file_put_contents($this->getExportFilePath(), $contents, FILE_APPEND);
    }
}