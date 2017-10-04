<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

class Environment
{
    /**
     * @var string
     */
    private $name = '';

    /**
     * @var DatabaseSettings
     */
    private $databaseSettings;

    /**
     * @var DatabaseConnection
     */
    private $databaseConnection;

    /**
     * @var PlatformConfiguration
     */
    private $platformConfiguration;

    /**
     * @var array
     */
    private $replacements = [];

    /**
     * @return array
     */
    public function getReplacements()
    {
        return $this->replacements;
    }

    /**
     * @param array $replacements
     */
    public function setReplacements($replacements)
    {
        $this->replacements = $replacements;
    }

    /**
     * @return PlatformConfiguration
     */
    public function getPlatformConfiguration()
    {
        return $this->platformConfiguration;
    }

    /**
     * @param PlatformConfiguration $platformConfiguration
     */
    public function setPlatformConfiguration(PlatformConfiguration $platformConfiguration)
    {
        $this->platformConfiguration = $platformConfiguration;
    }

    /**
     * @return string
     */
    public function getPathToEnvironmentConfig()
    {
        return Configuration::getInstance()->getPathToEnvironments() . '/' . $this->getName();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return DatabaseSettings
     */
    public function getDatabaseSettings()
    {
        return $this->databaseSettings;
    }

    /**
     * @param DatabaseSettings $databaseSettings
     */
    public function setDatabaseSettings(DatabaseSettings $databaseSettings)
    {
        $this->databaseSettings = $databaseSettings;
    }

    /**
     * @return DatabaseConnection
     */
    public function getDatabaseConnection()
    {
        return $this->databaseConnection;
    }

    /**
     * @param DatabaseConnection $databaseConnection
     */
    public function setDatabaseConnection(DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }
}