<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

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
     */
    public function generate(array $comparisonTables)
    {
        try {
            $this->saveHeader();

            // Process tables to drop and create
            $tablesDifference = $this->tablesDifference($comparisonTables);

            // Compare list of fields in tables
            // Compare data in tables
            // Compare indexes
            // Save migration to database

            if (!$tablesDifference) {
                $this->getSender()->info('No changes found.');
                $this->removeMigrationFile();
                return;
            }

            $this->recordMigration();
            $this->getSender()->info('Migration has been created: ' . $this->getFilename());
        } catch (\Exception $exception) {
            $this->getSender()->fatalError($exception->getMessage());
            $this->removeMigrationFile();
        }
    }

    private function removeMigrationFile()
    {
        unlink($this->getFilePath());
    }

    /**
     * Record migration to database.
     */
    private function recordMigration()
    {
        $sql = 'INSERT INTO ' . Configuration::getInstance()->getMigrationsTableName() . ' SET id=:id, name =:name, ts=:ts';
        $sqlVars = [
            ':id' => $this->getId(),
            ':name' => $this->getName(),
            ':ts' => time()
        ];

        $this->getSender()->getDatabaseConnection()->exec($sql, $sqlVars);
    }

    /**
     * @param array $comparisonTables
     * @return bool
     */
    private function tablesDifference(array $comparisonTables)
    {
        $difference = new TablesDifference($this->getSender()->getDatabaseConnection(), $comparisonTables);
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
    private function appendMigrationFile($contents)
    {
        file_put_contents($this->getFilePath(), $contents, FILE_APPEND);
    }

    private function saveHeader()
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
        file_put_contents($this->getFilePath(), $header);
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

        $this->id = date('Ydm-His-');
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

    public function importTemporarily()
    {
        $parser = new MigrationFileParser($this->getFilePath());
        $contents = $parser->getApply();
        unset($parser);

        if (empty($contents)) {
            return;
        }

        $this->getSender()->getDatabaseConnection()->exec("SET SQL_MODE='ALLOW_INVALID_DATES'");
        $prefix = Configuration::getInstance()->getTempTablePrefix();
        foreach ($contents as $item) {
            $item = preg_replace("/(.*)\{\{\:(.*)\:\}\}(.*)/", "$1" . $prefix . "$2$3", $item);
            $this->getSender()->getDatabaseConnection()->exec($item);
        }
    }
}