<?php

namespace Voyage;

use Symfony\Component\Console\Input\InputOption;

class InitCommand extends Command
{
    public function __construct()
    {
        $this->setName('init');
        $this->setDescription('Initialize voyage in the current working directory');

        parent::__construct();

        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Continue execution and overwrite existing .voyage configuration and clean existing migrations.');
        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Specify a platform, in this case Voyage will detect database connection settings automatically. For example: --config=wordpress.', 'wordpress');
        $this->addOption('host', '', InputOption::VALUE_REQUIRED, 'Database host (and port, port is optional)', 'localhost:3306');
        $this->addOption('user', 'u', InputOption::VALUE_REQUIRED, 'Database username');
        $this->addOption('pass', 'p', InputOption::VALUE_REQUIRED, 'Database password');
        $this->addOption('db', 'd', InputOption::VALUE_REQUIRED, 'Database name');
    }
}