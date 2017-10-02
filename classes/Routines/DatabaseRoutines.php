<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Configuration\Ignore;
use Voyage\Core\Routine;
use Voyage\Core\TableData;

/**
 * Class DatabaseRoutines
 * @package Voyage\Routines
 */
class DatabaseRoutines extends Routine
{
    /**
     * Remove voyage table with a list of migrations if it exists.
     */
    public function clean()
    {
        $sql = sprintf('DROP TABLE IF EXISTS `%s`', $this->getConfiguration()->getMigrationsTableName());
        $this->getDatabaseConnection()->exec($sql);
    }

    /**
     * Create voyage table
     */
    public function createTable()
    {
        $sql = "
            CREATE TABLE %s (
              `id` VARCHAR(50) NOT NULL DEFAULT '',
              `name` VARCHAR(255) NOT NULL DEFAULT '', 
              `ts` INT(11) NOT NULL DEFAULT 0,
              UNIQUE key (`id`)
            ) ENGINE=MyISAM
        ";

        $sql = sprintf($sql, $this->getConfiguration()->getMigrationsTableName());

        try {
            $this->getDatabaseConnection()->exec($sql);
            $this->getSender()->report('Created migrations table.');
        } catch (\Exception $e) {
            $this->getSender()->fatalError($e->getMessage());
        }
    }

    /**
     * Get tables in current database connection with ignore tables filtered out.
     */
    public function getTables()
    {
        $sql = 'SHOW TABLES';
        $tables = [];
        try {
            $stmt = $this->getDatabaseConnection()->query($sql);
            while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
                $tableName = $row[0];
                $ignoreMode = Ignore::shouldIgnore($tableName);

                if ($ignoreMode != Ignore::IgnoreFully) {
                    $tables[$tableName] = new TableData($tableName, $ignoreMode == Ignore::IgnoreDataOnly);
                }
            }
        } catch (\Exception $e) {
            $this->getSender()->fatalError($e->getMessage());
        }

        return $tables;
    }

    /**
     * Check whether we have permissions to run SELECT, INSERT, UPDATE, DROP, CREATE, ALTER, DELETE, INDEX, REFERENCES.
     * @throws \Exception
     */
    public function checkPermissions()
    {
        $allowed = false;
        $requiredPermissions = ['SELECT', 'INSERT', 'UPDATE', 'DROP', 'CREATE', 'ALTER', 'DELETE', 'INDEX', 'REFERENCES'];
        $missingPermissions = '';

        $sql = 'SHOW GRANTS FOR CURRENT_USER()';
        $stmt = $this->getDatabaseConnection()->query($sql);
        $pattern = '/GRANT\s+(.*)\s+ON\s+(.*)\s+TO\s+(.*)/i';

        while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $permission = $row[0];
            if (strpos($permission, 'GRANT USAGE') !== false || strpos($permission, 'GRANT PROXY') !== false) {
                continue;
            }

            $matches = [];
            if (preg_match($pattern, $permission, $matches)) {
                $databaseName = trim($matches[2]);
                $privileges = str_replace('ALL PRIVILEGES', 'ALL', trim($matches[1]));

                if ($databaseName == '*.*' || $databaseName == '`' . $this->getDatabaseConnection()->getSettings()->getDatabaseName() . '`.*') {
                    // Permission to all tables or the current database.
                    if ($privileges == 'ALL') {
                        $allowed = true;
                        break;
                    }

                    $permissions = array_map('trim', explode(',', $privileges));
                    $diff = array_diff($requiredPermissions, $permissions);
                    if (!empty($diff)) {
                        $missingPermissions = $diff;
                    } else {
                        $allowed = true;
                        break;
                    }
                }
            }
        }

        if (!$allowed) {
            if (empty($missingPermissions)) {
                $missingPermissions = $requiredPermissions;
            }

            $missingPermissionsList = implode(', ', $missingPermissions);
            throw new \Exception("Current user doesn't have sufficient permissions to the database. Missing permission(s): " . $missingPermissionsList);
        }
    }
}