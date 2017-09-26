<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers;

class PlatformConfigurations
{
    const AllowedConfigurations = ['auto', 'none', 'wordpress', 'magento1', 'magento2'];

    public static function isAllowed($configurationName)
    {
        return in_array($configurationName, PlatformConfigurations::AllowedConfigurations);
    }
}