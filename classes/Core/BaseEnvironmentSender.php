<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

class BaseEnvironmentSender
{
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
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->sender->getEnvironment();
    }

    /**
     * @param EnvironmentControllerInterface $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    public function __construct(EnvironmentControllerInterface $sender)
    {
        $this->sender = $sender;
    }
}