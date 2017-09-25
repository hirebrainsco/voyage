<?php

namespace Voyage\Core;

use Symfony\Component\Console\Output\OutputInterface;

class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var null | Configuration
     */
    private $configuration = null;

    public function __construct()
    {
        parent::__construct();

        $this->configuration = new Configuration();
    }

    /**
     * @return null|Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    protected function displayAppName(OutputInterface $output)
    {
        if (!$output->isQuiet()) {
            $output->writeln('<options=bold>' . $this->getApplication()->getName() . '</>');
        }
    }

    protected function checkIntegrity(OutputInterface $output)
    {
        $this->configuration->checkIntegrity();
    }
}