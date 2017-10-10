<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

use Voyage\MigrationActions\ActionsRunner;
use Voyage\Routines\DatabaseRoutines;
use Voyage\Routines\DataDifference;
use Voyage\Routines\FieldsDifference;
use Voyage\Routines\FileSystemRoutines;
use Voyage\Routines\TablesDifference;

/**
 * Class Migration
 * @package Voyage\Core
 */
class Migration extends BaseEnvironmentSender
{
    /**
     * @var string
     */
    private $id = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @return int
     */
    public function getTimestamp()
    {
        if ($this->timestamp <= 0) {
            return time();
        }

        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @var int
     */
    private $timestamp = 0;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @throws \Exception
     */
    public function setName($name)
    {
        if (empty($name)) {
            throw new \Exception('Migration name cannot be empty!');
        }

        $this->name = $name;
    }

    /**
     * @param array $comparisonTables
     * @return bool
     */
    public function generate(array $comparisonTables)
    {
        try {
            // Process tables to drop and create
            $tablesDifference = $this->tablesDifference($comparisonTables);

            // Compare list of fields in tables
            $fieldApplyActions = [];
            $fieldsDifference = $this->fieldsDifference($comparisonTables, $fieldApplyActions);

            $this->prepareTemporaryTables($fieldApplyActions);

            // Compare data in tables
            $dataDifference = $this->dataDifference($comparisonTables);

            if (!$tablesDifference && !$fieldsDifference && !$dataDifference) {
                $this->getSender()->info('No changes have been found.');
                $this->removeMigrationFile();
                return false;
            }

            return true;
        } catch (\Exception $exception) {
            $this->getSender()->fatalError($exception->getMessage());
            $this->removeMigrationFile();
            return false;
        }
    }

    /**
     * @param array $comparisonTables
     * @return bool
     */
    private function dataDifference(array $comparisonTables)
    {
        if (empty($comparisonTables)) {
            return false;
        }

        $this->getSender()->report('Checking differences in data.');

        $difference = new DataDifference($this->getSender(), $comparisonTables, $this);
        $hasData = $difference->getDifference();
        unset($difference);

        return $hasData;
    }

    /**
     * @param array $comparisonTables
     * @param array $fieldApplyActions
     * @return bool
     */
    private function fieldsDifference(array $comparisonTables, array &$fieldApplyActions)
    {
        if (empty($comparisonTables)) {
            return false;
        }

        $this->getSender()->report('Checking differences in fields.');

        $difference = new FieldsDifference($this->getSender(), $comparisonTables);
        $code = $difference->getDifferenceWithActions($fieldApplyActions);
        unset($difference);

        if (empty($code)) {
            return false;
        }

        $this->appendMigrationFile($code);
        unset($code);

        return true;
    }

    /**
     * Remove migration file.
     */
    public function removeMigrationFile()
    {
        if (file_exists($this->getFilePath())) {
            unlink($this->getFilePath());
        }
    }

    /**
     * @return bool|string
     */
    public function getFileContents()
    {
        if ($this->migrationFileExists()) {
            return file_get_contents($this->getFilePath());
        }

        return '';
    }

    /**
     * @return bool
     */
    public function migrationFileExists()
    {
        return file_exists($this->getFilePath());
    }

    /**
     * @param bool $registerMigration
     * @throws \Exception
     */
    public function apply($registerMigration = true)
    {
        if ($this->getId() == '') {
            throw new \Exception('Migration\'s ID cannot be empty!');
        }

        if (!$this->migrationFileExists()) {
            throw new \Exception('Migration\'s file doesn\'t exist at "' . $this->getFilePath() . '"');
        }

        $parser = new MigrationFileParser($this->getFilePath());
        $queries = $parser->getApply();
        $this->getSender()->getDatabaseConnection()->setVariables();

        $replacementsApplier = new Replacements($this->getSender()->getEnvironment()->getReplacements());
        foreach ($queries as $query) {
            $query = DatabaseRoutines::replaceTableNames($query);
            $query = $replacementsApplier->replace($query);
            $this->getSender()->getDatabaseConnection()->query($query);
        }

        unset($replacementsApplier);

        if ($registerMigration) {
            $this->setName($parser->getDescription());
            $this->setTimestamp($parser->getTimestamp());
            $this->recordMigration();
        }

        unset($parser);
    }

    /**
     * @param bool $removeMigrationFile
     * @param bool $unRegisterMigration
     * @throws \Exception
     */
    public function rollback($removeMigrationFile = true, $unRegisterMigration = true)
    {
        if ($this->getId() == '') {
            throw new \Exception('Migration\'s ID cannot be empty!');
        }

        if (!$this->migrationFileExists()) {
            throw new \Exception('Migration\'s file doesn\'t exist at "' . $this->getFilePath() . '"');
        }

        $parser = new MigrationFileParser($this->getFilePath());
        $queries = $parser->getRollback();
        unset($parser);

        $this->getSender()->getDatabaseConnection()->setVariables();

        foreach ($queries as $query) {
            $query = DatabaseRoutines::replaceTableNames($query);
            $this->getSender()->getDatabaseConnection()->exec($query);
        }

        if ($removeMigrationFile) {
            $this->removeMigrationFile();
        }

        if ($unRegisterMigration) {
            $this->unRegisterMigration();
        }
    }

    /**
     * @throws \Exception
     */
    public function unRegisterMigration()
    {
        if ($this->getId() == '') {
            throw new \Exception('Migration\'s ID cannot be empty!');
        }

        $sql = 'DELETE FROM `' . Configuration::getInstance()->getMigrationsTableName() . '` WHERE id=:id';
        $sqlVars = [
            ':id' => $this->getId()
        ];

        $this->getSender()->getDatabaseConnection()->exec($sql, $sqlVars);
    }

    /**
     * Record migration to database.
     * @throws \Exception
     */
    public function recordMigration()
    {
        if ($this->getId() == '') {
            throw new \Exception('Migration\'s ID cannot be empty!');
        }

        $sql = 'INSERT INTO ' . Configuration::getInstance()->getMigrationsTableName() . ' SET id=:id, name =:name, ts=:ts';
        $sqlVars = [
            ':id' => $this->getId(),
            ':name' => $this->getName(),
            ':ts' => $this->getTimestamp()
        ];

        $this->getSender()->getDatabaseConnection()->exec($sql, $sqlVars);
    }

    /**
     * @param array $comparisonTables
     * @return bool
     */
    private function tablesDifference(array $comparisonTables)
    {
        if (empty($comparisonTables)) {
            return false;
        }

        $this->getSender()->report('Checking differences in a list of database tables.');
        $difference = new TablesDifference($this->getSender(), $comparisonTables);
        $code = $difference->getDifference();
        unset($difference);

        if (empty($code)) {
            return false;
        }

        $this->appendMigrationFile($code);
        unset($code);

        return true;
    }

    /**
     * @param $contents
     */
    public function appendMigrationFile($contents)
    {
        file_put_contents($this->getFilePath(), $contents, FILE_APPEND);
    }

    /**
     * Save header of migration file.
     */
    public function saveHeader()
    {
        $name = $this->getName();
        $id = $this->getId();
        $environmentName = $this->getEnvironment()->getName();

        $header = <<< "EOD"
# Migration Name: $name
# Environment: $environmentName
# ID: $id
# 


EOD;

        $fs = new FileSystemRoutines($this->getSender());
        $fs->prepend($this->getFilePath(), $header);
        unset($fs);
    }

    public function printSuccess()
    {
        $this->getSender()->info('Migration has been created: ' . $this->getFilename());
    }

    /**
     * @return string
     */
    private function getFilePath()
    {
        return Configuration::getInstance()->getPathToMigrations() . '/' . $this->getFilename();
    }

    /**
     * @return false|string
     */
    private function getFilename()
    {
        return $this->getId() . '.mgr';
    }

    /**
     * @return string
     */
    private function getId()
    {
        if (!empty($this->id)) {
            return $this->id;
        }

        $this->id = date('Ymd-His-');
        $this->id .= strtolower(str_replace('.', '_', $this->getEnvironment()->getName()));
        return $this->id;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Import migration to temporary table.
     */
    public function importTemporarily()
    {
        $parser = new MigrationFileParser($this->getFilePath());
        $contents = $parser->getApply();
        unset($parser);

        if (empty($contents)) {
            return;
        }

        $this->getSender()->getDatabaseConnection()->setVariables();
        $prefix = Configuration::getInstance()->getTempTablePrefix();

        foreach ($contents as $item) {
            $item = DatabaseRoutines::replaceTableNames($item, $prefix);
            $this->getSender()->getDatabaseConnection()->exec($item);
        }
    }

    /**
     * @param array $fieldApplyActions
     */
    private function prepareTemporaryTables(array $fieldApplyActions)
    {
        static $applied = false;
        if ($applied) {
            return;
        }

        $actionsRunner = new ActionsRunner($this->getEnvironment()->getDatabaseConnection(), $fieldApplyActions, true);
        $actionsRunner->apply();
        unset($actionsRunner);
    }
}