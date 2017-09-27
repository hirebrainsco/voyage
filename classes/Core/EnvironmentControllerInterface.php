<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

interface EnvironmentControllerInterface extends InputOutputInterface, DatabaseConnectionInterface
{
    /**
     * @return Environment
     */
    public function getEnvironment();
}