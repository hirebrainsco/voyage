<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Configuration\EnvironmentConfig;
use Voyage\Configuration\EnvironmentConfigData;
use Voyage\Core\Configuration;
use Voyage\Core\DatabaseConnection;
use Voyage\Core\DatabaseSettings;
use Voyage\Core\Environment;

/**
 * Class EnvironmentsFactory
 * @package Voyage\Routines
 */
class EnvironmentsFactory
{
    /**
     * @var string
     */
    private $environmentName = '';

    /**
     * @param string $environmentName
     * @throws \Exception
     */
    public function setEnvironmentName($environmentName)
    {
        if (empty($environmentName)) {
            throw new \Exception('Environment name cannot be empty!');
        }

        $this->environmentName = $environmentName;
    }

    /**
     * @return string
     */
    public function getEnvironmentName()
    {
        return $this->environmentName;
    }

    /**
     * EnvironmentsFactory constructor.
     * @param $environmentName
     */
    public function __construct($environmentName)
    {
        $this->setEnvironmentName($environmentName);
        $this->create();
    }

    /**
     * @return Environment
     */
    public function create()
    {
        $this->checkEnvironmentConfig();

        $environmentConfig = new EnvironmentConfig(null);
        $environmentConfig->setFilename($this->getEnvironmentName());
        $configData = $environmentConfig->getData();

        $environment = new Environment();
        $environment->setName($this->getEnvironmentName());
        $environment->setDatabaseSettings($this->toDatabaseSettings($configData));
        $environment->setDatabaseConnection(new DatabaseConnection($environment->getDatabaseSettings()));
        return $environment;
    }

    private function toDatabaseSettings(EnvironmentConfigData $configData)
    {
        if (empty($configData->host) || empty($configData->username) || empty($configData->database)) {
            throw new \Exception('Database settings cannot be empty!');
        }

        $databaseSettings = new DatabaseSettings();
        $databaseSettings->setHost($configData->host);
        $databaseSettings->setDatabaseName($configData->database);
        $databaseSettings->setUsername($configData->username);
        $databaseSettings->setPassword($configData->password);

        return $databaseSettings;
    }

    /**
     * @return string
     */
    private function getPathToEnvironmentConfig()
    {
        return Configuration::getInstance()->getPathToEnvironments() . '/' . $this->getEnvironmentName();
    }

    /**
     * @throws \Exception
     */
    private function checkEnvironmentConfig()
    {
        $path = $this->getPathToEnvironmentConfig();
        if (!file_exists($path) || !is_readable($path) || !is_file($path)) {
            throw new \Exception("Environment config doesn't exist at '" . $path . "'");
        }
    }
}