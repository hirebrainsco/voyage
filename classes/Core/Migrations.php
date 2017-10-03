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
        $this->dropTemporaryTables();

        $migrations = $this->getListOfAppliedMigrations();  // Get list of migrations that have been applied (from migrations table).
        if (empty($migrations)) {
            return;
        }

        $this->getSender()->report('Pushing migrations for comparison.');
        $this->checkMigrationFiles($migrations);
        $this->importMigrations($migrations);
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
            $sql = "DROP TABLE IF EXISTS `" . $tableName . "`";
            $this->getSender()->getDatabaseConnection()->exec($sql);
        }
    }

    /**
     * @return array
     */
    public function getListOfAppliedMigrations()
    {
        $sql = 'SELECT * FROM ' . Configuration::getInstance()->getMigrationsTableName() . ' ORDER BY ts, id';
        $stmt = $this->getSender()->getDatabaseConnection()->query($sql);

        $migrations = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $migrations[] = $row;
        }

        return $migrations;
    }
}