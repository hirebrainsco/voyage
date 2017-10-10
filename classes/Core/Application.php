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
    const Version = '1.0.1';

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
        $this->application->setVersion(self::Version);

        $this->application->add(new \Voyage\Commands\Init());
        $this->application->add(new \Voyage\Commands\Status());
        $this->application->add(new \Voyage\Commands\Make());
        $this->application->add(new \Voyage\Commands\Rollback());
        $this->application->add(new \Voyage\Commands\Apply());
        $this->application->add(new \Voyage\Commands\ListCommand());
        $this->application->add(new \Voyage\Commands\DefaultCommand());
        $this->application->add(new \Voyage\Commands\Backup());
        $this->application->add(new \Voyage\Commands\Restore());

        $this->application->find('default')->setHidden(true);
        $this->application->setDefaultCommand('default');
    }
}

