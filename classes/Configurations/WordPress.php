<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configurations;

use Voyage\Core\DatabaseSettings;
use Voyage\Core\PlatformConfiguration;

/**
 * Class WordPress
 * @package Voyage\Configurations
 */
class WordPress extends PlatformConfiguration
{
    protected $name = 'wordpress';
    protected $defaultTablePrefix = 'wp_';

    /**
     * WordPress constructor.
     */
    public function __construct()
    {
        $this->pathToConfig = VOYAGE_WORKING_DIR . '/wp-config.php';
    }

    /**
     * @return array
     */
    protected function extract()
    {
        $result = parent::extract();
        $contents = file_get_contents($this->pathToConfig);

        if (empty($contents)) {
            return $result;
        }

        $matches = [];
        preg_match('/define.*DB_NAME.*(\'|\")(.*)(\'|\")/', $contents, $matches);
        if (!empty($matches) && !empty($matches[2])) {
            $result['name'] = $matches[2];
        }

        preg_match('/define.*DB_USER.*(\'|\")(.*)(\'|\")/', $contents, $matches);
        if (!empty($matches) && !empty($matches[2])) {
            $result['user'] = $matches[2];
        }

        preg_match('/define.*DB_PASSWORD.*(\'|\")(.*)(\'|\")/', $contents, $matches);
        if (!empty($matches) && !empty($matches[2])) {
            $result['pass'] = $matches[2];
        }

        preg_match('/define.*DB_HOST.*(\'|\")(.*)(\'|\")/', $contents, $matches);
        if (!empty($matches) && !empty($matches[2])) {
            $result['host'] = $matches[2];
        }

        preg_match('/\$table_prefix.*(\'|\")(.*)(\'|\")/', $contents, $matches);
        if (!empty($matches) && !empty($matches[2])) {
            $result['prefix'] = $matches[2];
        }

        return $result;
    }

    public function getIgnoreTables()
    {
        return [
            '~aiowps_events',
            '~aiowps_failed_logins',
            '~aiowps_global_meta',
            '~aiowps_login_activity',
            '~aiowps_login_lockdown',
            '~aiowps_permanent_block',
            '~blc_filters',
            '~blc_instances',
            '~blc_links',
            '~blc_synch',
            '~duplicator_packages',
            '~ewwwio_images',
            '~gpi_page_blacklist',
            '~gpi_page_reports',
            '~gpi_page_stats',
            '~mainwp_stream',
            '~mainwp_stream_context',
            '~mainwp_stream_meta',
            '~rg_incomplete_submissions',
            '~rg_lead',
            '~rg_lead_detail',
            '~rg_lead_detail_long',
            '~rg_lead_meta',
            '~rg_lead_notes',
            '~stream',
            '~stream_meta',
            '~wfBadLeechers',
            '~wfBlockedIPLog',
            '~wfBlocks',
            '~wfBlocksAdv',
            '~wfCrawlers',
            '~wfFileMods',
            '~wfHits',
            '~wfHoover',
            '~wfIssues',
            '~wfKnownFileList',
            '~wfLockedOut',
            '~wfLocs',
            '~wfLogins',
            '~wfNet404s',
            '~wfNotifications',
            '~wfPendingIssues',
            '~wfReverseCache',
            '~wfSNIPCache',
            '~wfScanners',
            '~wfStatus',
            '~wfThrottleLog',
            '~wfVulnScanners',
            '~WP_SEO_*',
        ];
    }
}