<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers\ConfigFiles;

use Voyage\Core\DatabaseConnectionWithIoInterface;

class ConfigFiles
{
    /**
     * @var DatabaseConnectionWithIoInterface
     */
    private $sender;

    /**
     * ConfigFiles constructor.
     * @param DatabaseConnectionWithIoInterface $sender
     */
    public function __construct(DatabaseConnectionWithIoInterface $sender)
    {
        $this->sender = $sender;
    }

    public function createEmptyFiles()
    {
        $ignore = new Ignore();
        $ignore->createEmptyFile();

        $dbSettings = new DbSettings();
        $dbSettings->setSender($this->sender);
        $dbSettings->createEmptyFile();

        unset($ignore, $dbSettings);
    }
}