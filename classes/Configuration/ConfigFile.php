<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configuration;

use Voyage\Core\Configuration;
use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Helpers\StringUtils;

/**
 * Class ConfigFile
 * @package Voyage\Configuration
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
     * @var EnvironmentControllerInterface
     */
    private $sender;

    /**
     * @return EnvironmentControllerInterface
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param EnvironmentControllerInterface $sender
     */
    public function setSender(EnvironmentControllerInterface $sender)
    {
        $this->sender = $sender;
    }

    /**
     * @return string
     */
    public function getBasePath()
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
    public function createConfig()
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

    /**
     * Read config and get its contents.
     * @param bool $removeComments
     * @param bool $removeEmptyLines
     * @return bool|string
     */
    protected function getConfigContents($removeComments = true, $removeEmptyLines = true)
    {
        if (!file_exists($this->getFilePath())) {
            return '';
        }

        $contents = file_get_contents($this->getFilePath());
        if (true === $removeComments) {
            $contents = StringUtils::removeComments($contents);
        }

        if (true === $removeEmptyLines) {
            $contents = StringUtils::removeEmptyLines($contents);
        }

        return $contents;
    }
}