<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Commands;

use Voyage\Core\Command;

/**
 * Class Status
 * @package Voyage\Commands
 */
class Status extends Command
{
    /**
     * Status constructor.
     */
    public function __construct()
    {
        $this->setName('status');
        $this->setDescription('Check current status (current migration and list of migrations that hasn\'t been imported yet).');

        parent::__construct();
    }
}