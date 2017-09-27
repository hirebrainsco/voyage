<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

interface DatabaseConnectionInterface
{
    /**
     * @return DatabaseConnection
     */
    public function getDatabaseConnection();

    /**
     * @return DatabaseSettings
     */
    public function getDatabaseSettings();
}