<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configuration;

/**
 * Class EnvironmentConfigData
 * @package Voyage\Configuration
 */
class EnvironmentConfigData
{
    /**
     * @var string
     */
    public $database = '';
    /**
     * @var string
     */
    public $username = '';
    /**
     * @var string
     */
    public $password = '';
    /**
     * @var string
     */
    public $host = '';
    /**
     * @var array
     */
    public $replacements = [];
}