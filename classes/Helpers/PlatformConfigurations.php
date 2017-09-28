<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers;

use Voyage\Core\DatabaseSettings;
use Voyage\Core\PlatformConfiguration;

/**
 * Class PlatformConfigurations
 * @package Voyage\Helpers
 */
class PlatformConfigurations
{
    const AutoDetect = 'auto';
    const None = 'none';

    /**
     * List of available configurations
     * @var array
     */
    private $configurations = [
        'wordpress' => ['class' => \Voyage\Configurations\WordPress::class, 'name' => 'WordPress'],
        'magento1' => ['class' => \Voyage\Configurations\MagentoOne::class, 'name' => 'Magento version 1.x'],
        'magento2' => ['class' => \Voyage\Configurations\MagentoTwo::class, 'name' => 'Magento version 2.x'],
    ];

    /**
     * Check whether the given configuration exists.
     * @param $configurationName
     * @return bool
     */
    public function exists($configurationName)
    {
        return isset($this->configurations[$configurationName]);
    }

    /**
     * Get instance of configuration class.
     * @param $configurationName
     * @return PlatformConfiguration
     * @throws \Exception
     */
    private function getConfigurationInstance($configurationName)
    {
        if (!isset($this->configurations[$configurationName])) {
            throw new \Exception("Configuration '" . $configurationName . "' doesn't exist!");
        }

        $className = $this->configurations[$configurationName]['class'];
        return new $className();
    }

    /**
     * Read configuration and fill database setting with read settings.
     * @param DatabaseSettings $databaseSettings
     * @param string $configurationName
     * @return PlatformConfiguration
     */
    public function read(DatabaseSettings $databaseSettings, $configurationName = PlatformConfigurations::AutoDetect)
    {
        $platformInstance = null;

        if ($configurationName !== PlatformConfigurations::AutoDetect) {
            if (!$this->exists($configurationName)) {
                return $platformInstance;
            }

            $configurationInstance = $this->getConfigurationInstance($configurationName);
            $settings = $configurationInstance->getDatabaseSettings();
            if (is_object($settings)) {
                $databaseSettings->copy($settings);
                $platformInstance = $configurationInstance;
            }

            unset($settings);
        } else {
            // Auto-detection
            foreach ($this->configurations as $name => $data) {
                $configurationInstance = $this->getConfigurationInstance($name);
                $settings = $configurationInstance->getDatabaseSettings();

                if (is_object($settings)) {
                    $databaseSettings->copy($settings);
                    $platformInstance = $configurationInstance;
                    break;
                } else {
                    unset($configurationInstance);
                }
            }
        }

        return $platformInstance;
    }
}