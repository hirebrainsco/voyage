<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Voyage\Configuration\CurrentEnvironment;

/**
 * Class Command
 * @package Voyage\Core
 */
abstract class Command extends \Symfony\Component\Console\Command\Command implements InputOutputInterface
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
     * @var Environment
     */
    private $environment;

    /**
     * @var ProgressBar
     */
    private $progressBar;

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
     * Initialize progress bar.
     */
    private function initializeProgressBar()
    {
        $this->progressBar = new ProgressBar($this->getOutput());
        $this->progressBar->setFormat('%message%');
    }

    /**
     * @return DatabaseSettings
     */
    public function getDatabaseSettings()
    {
        return $this->environment->getDatabaseSettings();
    }

    /**
     * @return DatabaseConnection
     */
    public function getDatabaseConnection()
    {
        return $this->environment->getDatabaseConnection();
    }

    /**
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param Environment $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * Command constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->configuration = Configuration::getInstance();
    }

    /**
     * Display progress message.
     * @param $message
     */
    public function reportProgress($message)
    {
        $this->progressBar->setMessage($message);
        $this->progressBar->display();
    }

    /**
     * Clear progress bar.
     */
    public function clearProgress()
    {
        $this->progressBar->finish();
        $this->progressBar->clear();
        echo PHP_EOL;
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

        $this->initializeProgressBar();
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
    public function isQuiet()
    {
        return $this->getOutput()->isQuiet();
    }

    /**
     * Output an informational message if no quiet parameter present.
     * @param $message
     */
    public function info($message)
    {
        $this->report(sprintf('<info>%s</info>', $message));
    }

    /**
     * Output a message if no quiet parameter present.
     * @param $message
     */
    public function report($message)
    {
        if ($this->isQuiet()) {
            return;
        }

        $this->writeln($message);
    }

    /**
     * Output fatal error and stop execution.
     * @param $message
     */
    public function fatalError($message)
    {
        $this->writeln(sprintf('<error>Fatal error: %s</error>', $message));
        exit(1);
    }

    /**
     * Wrapper for output writeln method.
     * @param $string
     */
    public function writeln($string)
    {
        $this->getOutput()->writeln($string);
    }

    /**
     * Display warning message.
     * @param $message
     */
    public function warning($message)
    {
        $this->writeln('<error>' . $message . '</error>');
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

    protected function initCurrentEnvironment()
    {
        $environment = new CurrentEnvironment();
        $environment->setSender($this);
        $this->setEnvironment($environment->getEnvironment());
    }
}