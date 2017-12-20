<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;


/**
 * Class Migrations
 * @package Voyage\Core
 */
class Migrations extends BaseEnvironmentSender
{
    /**
     * Import migrations to temp tables.
     */
    public function push()
    {
        $this->getSender()->report('Cleaning up temporary data.');
        $this->dropTemporaryTables();

        $migrations = $this->getAppliedMigrations();  // Get list of migrations that have been applied (from migrations table).
        if (empty($migrations)) {
            return;
        }

        $this->getSender()->report('Pushing migrations for comparison.');
        $this->checkMigrationFiles($migrations);
        $this->importMigrations($migrations);
    }

    /**
     * @return array
     */
    public function getLatestAppliedMigrationData()
    {
        $sql = 'SELECT * FROM `' . Configuration::getInstance()->getMigrationsTableName() . '` ORDER BY ts DESC LIMIT 1';
        $row = $this->getSender()->getDatabaseConnection()->fetch($sql);

        return $row;
    }

    /**
     * @param array $migrations
     */
    private function importMigrations(array $migrations)
    {
        foreach ($migrations as $migration) {
            $migrationInstance = new Migration($this->getSender());
            $migrationInstance->setId($migration['id']);
            $migrationInstance->importTemporarily();
            unset($migrationInstance);
        }
    }

    /**
     * @param array $migrations
     */
    private function checkMigrationFiles(array $migrations)
    {
        $basePath = Configuration::getInstance()->getPathToMigrations() . '/';
        foreach ($migrations as $migration) {
            $migrationPath = $basePath . $migration['id'] . '.mgr';
            if (!file_exists($migrationPath)) {
                $this->getSender()->fatalError('Migration "' . $migrationPath . '" cannot be found!');
            }
        }
    }

    /**
     * @return array
     */
    public function getNotAppliedMigrations()
    {
        $applied = $this->getAppliedMigrations();
        $all = $this->getAllMigrationFiles();
        $notApplied = [];

        foreach ($all as $filePath) {
            $id = pathinfo($filePath, PATHINFO_FILENAME);
            if (!isset($applied[$id])) {
                $notApplied[$id] = ['id' => $id, 'applied' => false];
            }
        }

        return $notApplied;
    }

    /**
     * @return array
     */
    public function getAllMigrationFiles()
    {
        $migrations = [];
        $basePath = Configuration::getInstance()->getPathToMigrations() . '/';

        $files = glob($basePath . '*.mgr');
        if (!empty($files)) {
            foreach ($files as $file) {
                $migrations[] = $file;
            }
        }

        return $migrations;
    }

    /**
     * @return array
     */
    public function getTemporaryTables()
    {
        $tables = [];
        $sql = "SHOW TABLES LIKE '" . Configuration::getInstance()->getTempTablePrefix() . "%'";
        $stmt = $this->getSender()->getDatabaseConnection()->query($sql);

        while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        return $tables;
    }

    /**
     * Drop temporary tables;
     */
    public function dropTemporaryTables()
    {
        $tables = $this->getTemporaryTables();
        if (empty($tables)) {
            return;
        }

        foreach ($tables as $tableName) {
            $this->getSender()->reportProgress('Temporary Table: `' . $tableName . '`');
            $sql = "DROP TABLE IF EXISTS `" . $tableName . "`";
            $this->getSender()->getDatabaseConnection()->exec($sql);
        }

        $this->getSender()->clearProgress();
    }

    /**
     * @return array
     */
    public function getAppliedMigrations()
    {
        $sql = 'SELECT * FROM ' . Configuration::getInstance()->getMigrationsTableName() . ' ORDER BY id';
        $stmt = $this->getSender()->getDatabaseConnection()->query($sql);

        $migrations = [];
        $lastId = '';
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['applied'] = true;
            $migrations[$row['id']] = $row;
            $lastId = $row['id'];
        }

        if (!empty($lastId)) {
            $migrations[$lastId]['current'] = true;
        }

        return $migrations;
    }

    /**
     * @return array
     */
    public function getAllMigrations()
    {
        $notApplied = $this->getNotAppliedMigrations();
        $applied = $this->getAppliedMigrations();

        $result = array_merge($applied, $notApplied);
        ksort($result);

        return $result;
    }
}