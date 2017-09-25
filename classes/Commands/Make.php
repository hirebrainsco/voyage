<?php

namespace Voyage\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Voyage\Core\Command;

class Make extends Command
{
    public function __construct()
    {
        $this->setName('make');
        $this->setDescription('Calculate difference between current database state and latest migration and create a new migration containing changes.');

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkIntegrity($output);
    }
}