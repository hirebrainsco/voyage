<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configuration;

use Voyage\Core\EnvironmentControllerInterface;

/**
 * Class ConfigFiles
 * @package Voyage\Configuration
 */
class ConfigFiles
{
    /**
     * @var EnvironmentControllerInterface
     */
    private $sender;

    /**
     * ConfigFiles constructor.
     * @param EnvironmentControllerInterface $sender
     */
    public function __construct(EnvironmentControllerInterface $sender)
    {
        $this->sender = $sender;
    }

    public function createConfigurationFiles()
    {
        $configs = [
            Ignore::class,
            GitIgnore::class,
            ApacheConfig::class,
            CurrentEnvironment::class,
            Environments::class
        ];

        foreach ($configs as $config) {
            $configInstance = new $config();
            $configInstance->setSender($this->sender);
            $configInstance->createConfig();
            unset($configInstance);
        }
    }
}