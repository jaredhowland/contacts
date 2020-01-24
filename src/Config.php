<?php

declare(strict_types=1);
/**
 * App configuration
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @version 2020-01-24
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
     */
    public static function setErrorReporting(): void
    {
        // Always report an error (but not always to end user)
        ini_set('error_reporting', '1');
        if (self::get('development')) {
            ini_set('display_errors', '1');
        } else {
            // Log all errors but do not display them to the end user
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
        }
    }

    /**
     * Get config setting from `.ini` file
     *
     * @param string $setting Setting name
     *
     * @return string Return setting value from `.ini` file
     */
    public static function get(string $setting): string
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
    private static function settingExists(string $setting): string
    {
        if (isset(self::$config[$setting])) {
            return self::$config[$setting];
        }

        throw new UnexpectedValueException(
            "'$setting' is not a valid config setting. Please check your 'config.ini' file for valid config options.\n"
        );
    }
}

Config::setErrorReporting();
