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

    /**
     * @return \Symfony\Component\Console\Application
     */
    public function getApplication();

    public function clearProgress();

    /**
     * @param string $message
     */
    public function reportProgress($message);
}