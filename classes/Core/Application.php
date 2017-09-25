<?php

namespace Voyage\Core;

use \Symfony\Component\Console\Application as SymfonyApplication;

class Application
{
    const Name = 'Voyage (Database Migration Tool)';
    const Version = '1.0.1';

    private $application = null;

    /**
     * @return null|SymfonyApplication
     */
    public function getApplication()
    {
        return $this->application;
    }

    public function __construct()
    {
        $this->application = new SymfonyApplication();
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
        $this->application->find('list')->setHidden(true);

        $this->application->add(new \Voyage\Commands\Init());
        $this->application->add(new \Voyage\Commands\Status());
        $this->application->add(new \Voyage\Commands\Make());
        $this->application->add(new \Voyage\Commands\Rollback());
        $this->application->add(new \Voyage\Commands\Apply());
        $this->application->add(new \Voyage\Commands\Reset());
        $this->application->add(new \Voyage\Commands\ListCommand());
    }
}

