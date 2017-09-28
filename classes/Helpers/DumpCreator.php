<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers;

use Voyage\Core\Routines;

class DumpCreator extends Routines
{
    private $destination = '';

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param string $destination
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    public function create()
    {
        if (empty($this->destination)) {
            throw new \Exception('Destination of the dump cannot be empty!');
        }
    }
}