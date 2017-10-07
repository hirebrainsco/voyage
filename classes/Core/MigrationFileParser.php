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
     * @return string
     */
    public function getDescription()
    {
        if ($this->getFilePath() == '' || !file_exists($this->getFilePath())) {
            return '';
        }

        $fileHandle = fopen($this->getFilePath(), 'r');
        if (is_resource($fileHandle)) {
            $buffer = fgets($fileHandle, 4096);
            fclose($fileHandle);

            $matches = [];
            if (preg_match("/\s*\#\s+Migration Name:\s+(.*)$/", $buffer, $matches)) {
                return $matches[1];
            }
        }

        return '';
    }

    public function getTimestamp()
    {
        if ($this->getFilePath() == '' || !file_exists($this->getFilePath())) {
            return '';
        }

        $filename = pathinfo($this->getFilePath(), PATHINFO_FILENAME);

        $matches = [];
        preg_match("/(\d{4})(\d{2})(\d{2})\-\d{4,}-\w/", $filename, $matches);

        if (!empty($matches) && sizeof($matches) == 4) {
            return strtotime($matches[1] . '-' . $matches[2] . '-' . $matches[3]);
        }

        return 0;
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