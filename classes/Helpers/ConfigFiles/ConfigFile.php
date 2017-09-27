<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers\ConfigFiles;

use Voyage\Core\Configuration;
use Voyage\Core\DatabaseConnectionWithIoInterface;

/**
 * Class ConfigFile
 * @package Voyage\Helpers\ConfigFiles
 */
abstract class ConfigFile
{
    /**
     * @var string
     */
    private $basePath = null;
    /**
     * @var string
     */
    protected $filename = '';

    /**
     * @var DatabaseConnectionWithIoInterface
     */
    private $sender;

    /**
     * @return DatabaseConnectionWithIoInterface
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param DatabaseConnectionWithIoInterface $sender
     */
    public function setSender(DatabaseConnectionWithIoInterface $sender)
    {
        $this->sender = $sender;
    }

    /**
     * @return string
     */
    final public function getBasePath()
    {
        if (!is_null($this->basePath)) {
            return $this->basePath;
        }

        $configuration = new Configuration();
        $this->basePath = $configuration->getPathToVoyage();
        unset($configuration);

        return $this->basePath;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->getBasePath() . '/' . $this->getFilename();
    }

    /**
     * Create an empty configuration file.
     */
    public function createEmptyFile()
    {
        file_put_contents($this->getFilePath(), $this->getTemplate());
    }

    /**
     * @return string
     */
    protected function getTemplate()
    {
        return '';
    }
}