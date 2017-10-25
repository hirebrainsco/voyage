<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

class Application
{
    const Name = 'Voyage (Database Migration Tool)';
    private $application = null;

    /**
     * @return null|SymfonyApplicatio
     */
    public function getApplication()
    {
        return $this->application;
    }

    public function __construct()
    {
        ini_set('memory_limit', -1);
        date_default_timezone_set('UTC');
        set_time_limit(0);

        $this->application = new ConsoleApplication();
    }

    public function run()
    {
        $this->configureCommands();
        $this->application->run();
    }

    private function configureCommands()
    {
        $this->application->setName(self::Name);
        $this->setVersion();

        $this->application->add(new \Voyage\Commands\Init());
        $this->application->add(new \Voyage\Commands\Status());
        $this->application->add(new \Voyage\Commands\Make());
        $this->application->add(new \Voyage\Commands\Rollback());
        $this->application->add(new \Voyage\Commands\Apply());
        $this->application->add(new \Voyage\Commands\ListCommand());
        $this->application->add(new \Voyage\Commands\DefaultCommand());
        $this->application->add(new \Voyage\Commands\Backup());
        $this->application->add(new \Voyage\Commands\Restore());
        $this->application->add(new \Voyage\Commands\SelfUpdate());

        $this->application->find('default')->setHidden(true);
        $this->application->setDefaultCommand('default');
    }

    private function setVersion()
    {
        $versionFilePath = __DIR__ . '/../../version';
        $version = 'UNKNOWN';

        if (file_exists($versionFilePath)) {
            $version = file_get_contents($versionFilePath);
        }

        $this->application->setVersion($version);
    }
}

