<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

abstract class QueryParser
{
    /**
     * @var string
     */
    private $query = '';

    /**
     * QueryParser constructor.
     * @param string $query
     * @throws \Exception
     */
    public function __construct($query)
    {
        $this->setQuery($query);
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $query
     * @throws \Exception
     */
    public function setQuery($query)
    {
        $query = trim($query);
        if (empty($query)) {
            throw new \Exception('SQL query cannot be empty!');
        }

        $this->query = $query;
    }

    /**
     * Extract table name from the query
     */
    public function getTableName()
    {
        return '';
    }
}