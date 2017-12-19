<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

use Voyage\Core\DatabaseConnection;
use Voyage\Core\Migration;
use Voyage\Core\Replacements;
use Voyage\Routines\DatabaseRoutines;

class RecordAction extends MigrationAction
{
    /**
     * @var null|array
     */
    protected static $replacements = null;

    /**
     * @var string
     */
    protected static $staticDataForTable = '';

    /**
     * @var string
     */
    protected static $primaryKey = '';

    /**
     * @var int
     */
    protected static $totalFields = 0;

    /**
     * @var array
     */
    protected static $fields = [];

    /**
     * @var \Voyage\Core\Environment
     */
    protected $environment;

    /**
     * @var \Voyage\Core\EnvironmentControllerInterface
     */
    protected $sender;

    /**
     * @var array
     */
    protected $row = [];

    /**
     * RecordAction constructor.
     * @param DatabaseConnection $connection
     * @param $tableName
     * @param $row
     * @param Migration $migration
     * @internal param Environment $environment
     */
    public function __construct(DatabaseConnection $connection, $tableName, array $row, Migration $migration)
    {
        parent::__construct($connection, $tableName);
        $this->environment = $migration->getEnvironment();
        $this->sender = $migration->getSender();
        $this->row = $row;
    }

    /**
     * Initialize static data
     */
    protected function prepareStaticData()
    {
        if (self::$staticDataForTable == $this->tableName) {
            return;
        }

        self::$primaryKey = '';
        self::$staticDataForTable = $this->tableName;
        self::$replacements = $this->environment->getReplacements();

        self::$fields = $this->getTableFields();
        self::$totalFields = sizeof(self::$fields);

        foreach (self::$fields as $field) {
            if ($field->isPrimaryKey()) {
                self::$primaryKey = $field->name;
                break;
            }
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function prepareValue($value)
    {
        return Replacements::prepareValue($value, self::$replacements);
    }

    /**
     * @return array
     */
    private function getTableFields()
    {
        $databaseRoutines = new DatabaseRoutines($this->sender);
        $fields = $databaseRoutines->getTableFields($this->tableName);
        unset($databaseRoutines);

        return $fields;
    }
}