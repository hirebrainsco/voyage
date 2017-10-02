<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;


class MigrationFileParser
{
    const Apply = '# @APPLY';
    const Rollback = '# @ROLLBACK';

    private $filePath = '';

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param string $filePath
     * @throws \Exception
     */
    public function setFilePath($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('Cannot find: ' . $filePath);
        }

        $this->filePath = $filePath;
    }

    /**
     * MigrationFileParser constructor.
     * @param $filePath
     */
    public function __construct($filePath)
    {
        $this->setFilePath($filePath);
    }

    /**
     * @param $startMarker
     * @param $endMarker
     * @return array
     * @throws \Exception
     */
    private function loadContents($startMarker, $endMarker)
    {
        $fileHandle = fopen($this->getFilePath(), 'r');
        if (!is_resource($fileHandle)) {
            throw new \Exception('Failed to read: ' . $this->getFilePath());
        }

        $contents = [];
        $harvesting = false;
        $queryCode = '';

        while (($line = fgets($fileHandle)) !== false) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            if (!$harvesting && $line == $startMarker) {
                $harvesting = true;
                $queryCode = '';
                continue;
            }

            if ($harvesting && $line == $endMarker) {
                $harvesting = false;
                $queryCode = '';
                continue;
            }

            if ($line[0] == '#' || !$harvesting) {
                continue;
            }

            $queryCode .= ' ' . $line;
            $matches = [];

            if (preg_match("/\A\s*(.*)\s*;\s*\z/", $queryCode, $matches)) {
                $queryCode = '';
                $contents[] = trim($matches[1]);
            }
        }

        fclose($fileHandle);
        return $contents;
    }

    /**
     * @return array
     */
    public function getApply()
    {
        return $this->loadContents(self::Apply, self::Rollback);
    }

    /**
     * @return array
     */
    public function getRollback()
    {
        return $this->loadContents(self::Rollback, self::Apply);
    }
}