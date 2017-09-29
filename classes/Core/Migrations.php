<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;


class Migrations extends BaseEnvironmentSender
{
    public function push()
    {
        $this->dropTemporaryTables();

        $migrations = $this->getListOfAppliedMigrations();  // Get list of migrations that have been applied (from migrations table).
        if (empty($migrations)) {
            return;
        }

        // TODO: check migration files
        // TODO: import migrations for checking and return list of tables that have been pushed.
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
            $sql = "DROP TABLE IF EXISTS '" . $tableName . "'";
            $this->getSender()->getDatabaseConnection()->exec($sql);
        }
    }

    /**
     * @return array
     */
    public function getListOfAppliedMigrations()
    {
        $sql = 'SELECT * FROM ' . Configuration::getInstance()->getMigrationsTableName() . ' ORDER BY id';
        $stmt = $this->getSender()->getDatabaseConnection()->query($sql);

        $migrations = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $migrations[] = $row;
        }

        return $migrations;
    }
}