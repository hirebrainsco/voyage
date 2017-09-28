<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

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
     * @var Environment
     */
    private $environment;

    /**
     * @param string $environmentName
     * @throws \Exception
     */
    public function setEnvironmentName($environmentName)
    {
        if (empty($this->environmentName)) {
            throw new \Exception('Environment name cannot be empty!');
        }

        $this->environmentName = $environmentName;
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

    private function create()
    {

    }
}