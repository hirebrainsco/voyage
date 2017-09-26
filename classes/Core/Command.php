<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Command
 * @package Voyage\Core
 */
class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Command constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->configuration = new Configuration();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        if (!is_object($this->input) || !is_object($this->output)) {
            throw new \Exception('I/O is not ready!');
        }
    }

    /**
     * @return null|Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Wrapper for output isQuiet method.
     * @return bool
     */
    protected function isQuiet()
    {
        return $this->getOutput()->isQuiet();
    }

    /**
     * Wrapper for output writeln method.
     * @param $string
     */
    protected function writeln($string)
    {
        $this->getOutput()->writeln($string);
    }

    /**
     * Display application's name.
     */
    protected function displayAppName()
    {
        if (!$this->isQuiet()) {
            $this->writeln('<options=bold>' . $this->getApplication()->getName() . '</>');
        }
    }

    /**
     * Check if voyage has been initialized, not locked and command can continue to run.
     * @param OutputInterface $output
     */
    protected function checkIntegrity(OutputInterface $output)
    {
        $this->configuration->checkIntegrity();
    }
}