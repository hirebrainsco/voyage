<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

/**
 * Class Migration
 * @package Voyage\Core
 */
class Migration
{
    /**
     * @var EnvironmentControllerInterface
     */
    private $sender;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @throws \Exception
     */
    public function setName($name)
    {
        if (empty($name)) {
            throw new \Exception('Migration name cannot be empty!');
        }

        $this->name = $name;
    }

    /**
     * Migration constructor.
     * @param EnvironmentControllerInterface $sender
     */
    public function __construct(EnvironmentControllerInterface $sender)
    {
        $this->sender = $sender;
    }
}