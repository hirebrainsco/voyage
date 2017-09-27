<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers\ConfigFiles;

use Voyage\Core\DatabaseConnectionWithIoInterface;

/**
 * Class ConfigFiles
 * @package Voyage\Helpers\ConfigFiles
 */
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

        $gitIgnore = new GitIgnore();
        $gitIgnore->createEmptyFile();

        $apacheConfig = new ApacheConfig();
        $apacheConfig->createEmptyFile();

        unset($ignore, $dbSettings, $gitIgnore, $apacheConfig);
    }
}