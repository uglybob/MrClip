<?php

namespace Uglybob\MrClip\Lib;

class Setup
{
    protected static $settings = [
        'url' => 'https://prmurl.com',
        'user' => 'user',
        'pass' => 'pass',
        'editor' => 'vim',
        'storage' => __DIR__ . '/../Storage',
    ];

    public static function getSettings()
    {
        return self::$settings;
    }

    public static function get($setting)
    {
        return self::$settings[$setting];
    }
}
