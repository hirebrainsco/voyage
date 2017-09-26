<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configurations;

use Voyage\Core\PlatformConfiguration;

/**
 * Class MagentoTwo
 * @package Voyage\Configurations
 */
class MagentoTwo extends PlatformConfiguration
{
    /**
     * MagentoOne constructor.
     */
    public function __construct()
    {
        $this->pathToConfig = VOYAGE_WORKING_DIR . '/app/etc/env.php';
    }

    /**
     * @return array
     */
    protected function extract()
    {
        $result = parent::extract();
        $config = include($this->pathToConfig);

        if (empty($config) || !isset($config['db']) || !isset($config['db']['connection']) || empty($config['db']['connection'])) {
            return $result;
        }

        foreach ($config['db']['connection'] as $connection) {
            if (!isset($connection['active']) || !$connection['active']) {
                continue;
            }

            if (isset($connection['host'])) {
                $result['host'] = $connection['host'];
            }

            if (isset($connection['dbname'])) {
                $result['name'] = $connection['dbname'];
            }

            if (isset($connection['username'])) {
                $result['user'] = $connection['username'];
            }

            if (isset($connection['password'])) {
                $result['pass'] = $connection['password'];
            }

            break;
        }

        return $result;
    }
}