<?php
/**
 * App configuration
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @version 2019-05-28
 * @since   2016-09-28
 */

namespace Contacts;

use UnexpectedValueException;

/**
 * Configuration class to import `Config.ini` file and set other defaults
 */
class Config
{
    /**
     * @var array|false $config Array of values in `Config.ini`. `false` if file cannot be parsed.
     */
    private static $config;

    /**
     * Set error reporting based on if app is in development or production
     *
     * @param null
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
    public static function get(string $setting)
    {
        if (empty(self::$config) && is_array(parse_ini_file('Config.ini'))) {
            self::$config = parse_ini_file('Config.ini');
        }

        return self::settingExists($setting);
    }

    /**
     * Check if setting in `.ini` file exists
     *
     * @param string $setting Setting name to check for
     *
     * @return string Setting value from `.ini` file
     *
     * @throws UnexpectedValueException when `$setting` does not exist
     */
    private static function settingExists(string $setting)
    {
        if (isset(self::$config[$setting])) {
            return self::$config[$setting];
        } else {
            throw new UnexpectedValueExceptionAlias("'$setting' is not a valid config setting. Please check your 'config.ini' file for valid config options.\n");
        }
    }
}

config::setErrorReporting();
