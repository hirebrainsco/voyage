<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configurations;

use Voyage\Core\DatabaseSettings;
use Voyage\Core\PlatformConfiguration;

/**
 * Class WordPress
 * @package Voyage\Configurations
 */
class WordPress extends PlatformConfiguration
{
    /**
     * @var string
     */
    protected $pathToConfig = '';

    /**
     * WordPress constructor.
     */
    public function __construct()
    {
        $this->pathToConfig = VOYAGE_WORKING_DIR . '/wp-config.php';
    }

    public function getDatabaseSettings()
    {
        if (!$this->configFileExists()) {
            return null;
        }

        $result = $this->extract();
        if (empty($result)) {
            return null;
        }

        $settings = new DatabaseSettings();

        $settings->setUsername($result['user']);
        $settings->setPassword($result['pass']);
        $settings->setDatabaseName($result['name']);
        $settings->setHost($result['host']);

        return $settings;
    }

    private function extract()
    {
        $result = [];
        $contents = file_get_contents($this->pathToConfig);

        if (empty($contents)) {
            return $result;
        }

        $matches = [];
        preg_match('/define.*DB_NAME.*(\'|\")(.*)(\'|\")/', $contents, $matches);
        if (!empty($matches) && !empty($matches[2])) {
            $result['name'] = $matches[2];
        }

        preg_match('/define.*DB_USER.*(\'|\")(.*)(\'|\")/', $contents, $matches);
        if (!empty($matches) && !empty($matches[2])) {
            $result['user'] = $matches[2];
        }

        preg_match('/define.*DB_PASSWORD.*(\'|\")(.*)(\'|\")/', $contents, $matches);
        if (!empty($matches) && !empty($matches[2])) {
            $result['pass'] = $matches[2];
        }

        preg_match('/define.*DB_HOST.*(\'|\")(.*)(\'|\")/', $contents, $matches);
        if (!empty($matches) && !empty($matches[2])) {
            $result['host'] = $matches[2];
        }

        return $result;
    }
}