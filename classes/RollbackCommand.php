<?php

namespace Voyage;

use Symfony\Component\Console\Input\InputOption;

class RollbackCommand extends Command
{
    public function __construct()
    {
        $this->setName('rollback');
        $this->setDescription('Rollback to previous migration.');

        parent::__construct();

        $this->addArgument('migration', 'm', 'Rollback a migration and apply changes to current database. If no parameters set the command will return to previous migration.', InputOption::VALUE_OPTIONAL);
    }
}