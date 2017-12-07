<?php
/**
 * App configuration
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @version 2017-12-05
 * @since   2016-09-28
 *
 */

namespace Contacts;

/**
 * Configuration class to import `config.ini` file and set other defaults
 */
class Config
{
    /**
     * @var string $config String returned by `config.ini`
     */
    private static $config;

    /**
     * Set error reporting based on if app is in development or production
     * @return null
     * @internal param $null
     *
     */
    public static function setErrorReporting()
    {
        if (self::get('development')) {
            ini_set('display_errors', '1');
            ini_set('error_reporting', E_ALL ^ E_NOTICE);
        } else {
            ini_set('display_errors', '0');
        }
    }

    /**
     * Get config setting from `.ini` file
     *
     * @param string $setting Setting name
     *
     * @return string Return setting value from `.ini` file
     */
    public static function get($setting)
    {
        if (self::$config === null) {
            self::$config = parse_ini_file('config.ini');
        }

        return self::settingExists($setting);
    }

    /**
     * Check if setting in `.ini` file exists
     *
     * @param string $setting Setting name to check for
     *
     * @return string Setting value from `.ini` file
     */
    private static function settingExists($setting)
    {
        if (isset(self::$config[$setting])) {
            return self::$config[$setting];
        } else {
            throw new \UnexpectedValueException("'$setting' is not a valid config setting. Please check your 'config.ini' file for valid config options.");
        }
    }

}

config::setErrorReporting();
date_default_timezone_set(\Contacts\Config::get('time_zone'));

?>
