<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Core\BaseEnvironmentSender;
use Voyage\Core\Migrations;

/**
 * Class PushMigrations
 * @package Voyage\Routines
 */
class PushMigrations extends BaseEnvironmentSender
{
    /**
     * @var string
     */
    private $prefix = 'tmp_voyage_';

    /**
     * @return array
     */
    public function push()
    {
        $this->dropTemporaryTables();

        // Get list of migrations
        $migrationsObject = new Migrations();
        $migrations = $migrationsObject->getList();
        unset($migrationsObject);

        if (empty($migrations)) {
            return [];
        }

        // TODO: import migrations for checking and return list of tables that have been pushed.
        return [];
    }

    /**
     * Drop temporary tables;
     */
    protected function dropTemporaryTables()
    {
        $sql = "SHOW TABLES LIKE '" . $this->prefix . "%'";
        $stmt = $this->getSender()->getDatabaseConnection()->query($sql);

        while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $tableName = $row[0];
            $sql = "DROP TABLE IF EXISTS '" . $tableName . "'";
            $this->getSender()->getDatabaseConnection()->exec($sql);
        }
    }
}