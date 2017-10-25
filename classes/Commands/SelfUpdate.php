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
use Voyage\Core\Configuration;
use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Core\Migration;
use Voyage\Core\Migrations;
use Voyage\Routines\DatabaseRoutines;

/**
 * Class SelfUpdate
 * @package Voyage\Commands
 */
class SelfUpdate extends Command implements EnvironmentControllerInterface
{
    /**
     * Apply constructor.
     */
    public function __construct()
    {
        $this->setName('selfupdate');
        $this->setDescription('Upgrade to latest version of voyage.');

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            parent::execute($input, $output);

            $this->displayAppName();

            $selfUpdate = new \Voyage\Core\SelfUpdate($this);
            $selfUpdate->update();
            unset($selfUpdate);
        } catch (\Exception $e) {
            $this->fatalError($e->getMessage());
        }
    }
}