<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configuration;

use Voyage\Core\Configuration;

class EnvironmentConfig extends ConfigFile
{
    public function createConfig()
    {
        $this->filename = $this->getSender()->getEnvironment()->getName();

        parent::createConfig();
    }

    protected function getTemplate()
    {
        $template = <<<'EOD'
# Environment: "{ENVIRONMENT_NAME}"
# Created on: {DATE}
# ----------------------------------------------------------------------------------------------------------------------
#
# Database Configuration
#
# ----------------------------------------------------------------------------------------------------------------------

host={DB_HOST}
database={DB_NAME}
username={DB_USERNAME}
password={DB_PASSWORD}

# ----------------------------------------------------------------------------------------------------------------------
#
# Replacement Variables
#
# A list of replacement variables which allow to adjust data to your current environment.
# Voyage will keep migrations data with these placeholders in GIT and will replace them with values set below for your
# current environment (replacement is also done in serialized arrays).
#
# This is usable when you have to store URLs, paths, etc. in database and need to change them every time when you deploy
# updates to remote server or synchronize your local development environment with remote server.
#
# Format:
#    VARIABLE=VALUE
#
# For example, on live server this file could contain:
#    SITEURL=www.example.com
#
# And in development environment:
#    SITEURL=localhost
#
# ----------------------------------------------------------------------------------------------------------------------

EOD;
        $variables = [
            'ENVIRONMENT_NAME' => $this->getSender()->getEnvironment()->getName(),
            'DATE' => date('d F Y'),
            'DB_HOST' => $this->getSender()->getDatabaseSettings()->getHost() . ':' . $this->getSender()->getDatabaseSettings()->getPort(),
            'DB_NAME' => $this->getSender()->getDatabaseSettings()->getDatabaseName(),
            'DB_USERNAME' => $this->getSender()->getDatabaseSettings()->getUsername(),
            'DB_PASSWORD' => $this->getSender()->getDatabaseSettings()->getPassword()
        ];

        foreach ($variables as $name => $value) {
            $template = str_replace('{' . $name . '}', $value, $template);
        }

        return $template;
    }

    public function getBasePath()
    {
        return Configuration::getInstance()->getPathToEnvironments();
    }

    /**
     * @return EnvironmentConfigData
     * @throws \Exception
     */
    public function getData()
    {
        $data = new EnvironmentConfigData();

        $databaseParams = ['host', 'username', 'database', 'password'];

        $contents = $this->getConfigContents();
        $items = explode("\n", $contents);

        if (empty($items)) {
            throw new \Exception('Environment config is empty!');
        }

        foreach ($items as $item) {
            $item = trim($item);
            if (empty($item)) {
                continue;
            }

            $var = explode("=", $item, 2);
            if (sizeof($var) != 2) {
                throw new \Exception('Syntax error in expression "' . $item . '" in "' . $this->getFilePath() . '"');
            }

            $varName = trim($var[0]);
            $varValue = trim($var[1]);

            if (empty($varName)) {
                throw new \Exception('Parameter name cannot be empty in "' . $this->getFilePath() . '"');
            }

            if (in_array($varName, $databaseParams)) {
                if (empty($varValue)) {
                    throw new \Exception('A value is required for parameter "' . $varName . '" in "' . $this->getFilePath() . '"');
                }

                $data->$varName = $varValue;
            } else {
                $data->replacements[] = [$varName, $varValue];
            }

        }

        return $data;
    }
}