<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

use Symfony\Component\Console\Helper\ProgressBar;

class SelfUpdate extends BaseEnvironmentSender
{
    const BaseUrl = 'http://voyage.hirebrains.co/latest/';

    private $targetVersion = '';

    public function update()
    {
        $this->checkWritablePermissions();
        $this->checkVersion();
        $this->downloadLatestRelease();
    }

    private function downloadLatestRelease()
    {
        $tempFileName = tempnam(sys_get_temp_dir(), 'voyage');

        if (!is_writable($tempFileName)) {
            $this->getSender()->fatalError('Cannot create temporary file at "' . $tempFileName . '".');
        }

        $output = $this->getSender()->getOutput();
        $progress = new ProgressBar($output);
        $ctx = stream_context_create([], ['notification' => function ($notificationCode, $severety, $message, $messageCode, $bytesTransferred, $bytesMax) use ($progress) {
            switch ($notificationCode) {
                case STREAM_NOTIFY_FILE_SIZE_IS:
                    $progress->start($bytesMax);
                    break;

                case STREAM_NOTIFY_PROGRESS:
                    $progress->setProgress($bytesTransferred);
                    break;
            }
        }]);

        $this->getSender()->report('Downloading latest release...');
        $file = @file_get_contents($this->getVoyageBinUrl(), false, $ctx);
        $progress->finish();
        $this->getSender()->report('');

        if (@file_put_contents($tempFileName, $file)) {
            $this->getSender()->info('Download successfully completed.');
        }


        $pathToPhar = $this->getPathToPhar();

        @unlink($pathToPhar);
        @rename($tempFileName, $pathToPhar);
        @chmod($pathToPhar, 0755);

        $this->getSender()->info('Successfully upgraded to version: ' . $this->targetVersion);
    }

    /**
     * @return string
     */
    public function getPathToPhar()
    {
        return \Phar::running(false);
    }

    /**
     * Check if Voyage can write to itself.
     */
    private function checkWritablePermissions()
    {
        $pathToPhar = $this->getPathToPhar();
        if (false === $pathToPhar) {
            $this->getSender()->fatalError('Executing "selfupdate" command outside of PHAR archive isn\'t allowed.');
        }

        if (!is_writable($pathToPhar)) {
            $this->getSender()->fatalError('Path "' . $pathToPhar . '" isn\'t writable.');
        } else {
            $this->getSender()->info('Voyage path is writable.');
        }
    }

    private function checkVersion()
    {
        $this->getSender()->report('Retrieving version number.');
        $latestVersion = @file_get_contents($this->getVersionUrl());

        if (empty($latestVersion)) {
            $this->getSender()->fatalError('Failed to get latest version number!');
        }

        $currentVersion = $this->getSender()->getApplication()->getVersion();

        $this->getSender()->report('Current version: ' . $currentVersion);
        $this->getSender()->report('Latest version: ' . $latestVersion);

        if ($currentVersion == $latestVersion) {
            $this->getSender('You have the latest version of Voyage.');
            exit();
        }

        $this->targetVersion = $latestVersion;
    }

    private function getVersionUrl()
    {
        return SelfUpdate::BaseUrl . 'version';
    }

    private function getVoyageBinUrl()
    {
        return SelfUpdate::BaseUrl . 'voyage';
    }
}