<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Voyage\Core\Command;

/**
 * Class Make
 * @package Voyage\Commands
 */
class Make extends Command
{
    /**
     * Make constructor.
     */
    public function __construct()
    {
        $this->setName('make');
        $this->setDescription('Calculate difference between current database state and latest migration and create a new migration containing changes.');

        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkIntegrity($output);
    }
}