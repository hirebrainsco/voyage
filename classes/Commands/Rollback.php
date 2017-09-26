<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Commands;

use Symfony\Component\Console\Input\InputOption;
use Voyage\Core\Command;

/**
 * Class Rollback
 * @package Voyage\Commands
 */
class Rollback extends Command
{
    /**
     * Rollback constructor.
     */
    public function __construct()
    {
        $this->setName('rollback');
        $this->setDescription('Rollback to previous migration.');

        parent::__construct();

        $this->addOption('migration', 'm', InputOption::VALUE_OPTIONAL, 'Rollback a migration and apply changes to current database. If no parameters set the command will return to previous migration.');
    }
}