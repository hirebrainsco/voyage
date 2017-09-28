<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configuration;

use Voyage\Core\Configuration;
use Voyage\Core\EnvironmentControllerInterface;

class Environments extends ConfigFile
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
        $configuration = new Configuration();
        $path = $configuration->getPathToEnvironments();
        unset($configuration);

        return $path;
    }
}