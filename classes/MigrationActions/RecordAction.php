<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

use Voyage\Core\DatabaseConnection;
use Voyage\Core\Migration;

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

        self::$staticDataForTable = $this->tableName;
        self::$replacements = $this->environment->getReplacements();
    }

    /**
     * @param string $value
     * @return string
     */
    protected function prepareValue($value)
    {
        if (!is_string($value)) {
            if (!$value) {
                return 'NULL';
            }
            return $value;
        }

        if (empty($value)) {
            return '\'\'';
        }

        if (!empty(self::$replacements)) {
            $serializedData = @unserialize($value);
            if (false !== $serializedData) {
                $replacements = self::$replacements;
                array_walk_recursive($serializedData, function (&$item, $key) use ($replacements) {
                    if (is_string($item)) {
                        foreach ($replacements as $replacement) {
                            $item = str_replace($replacement[1], $replacement[0], $item);
                        }
                    }
                });

                $value = serialize($serializedData);
            } else {
                foreach (self::$replacements as $replacement) {
                    $value = str_replace($replacement[1], $replacement[0], $value);
                }
            }
        }

        return '\'' . str_replace(["'", "\r", "\n", "\t"], ["\\'", '\\r', '\\n', '\\t'], $value) . '\'';
    }
}