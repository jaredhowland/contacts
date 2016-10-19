<?php
/**
  * App configuration
  *
  * @author Jared Howland <contacts@jaredhowland.com>
  * @version 2016-10-19
  * @since 2016-09-28
  *
  */

class config {
  private static $config;

  /**
   * Get config setting from `.ini` file
   *
   * @param string $setting Setting name
   * @return string Return setting value from `.ini` file
   */
  public static function get($setting) {
    if(self::$config === null) {
      self::$config = parse_ini_file('config.ini');
    }
    return self::setting_exists($setting);
  }

  /**
   * Check if setting in `.ini` file exists
   *
   * @param string $setting Setting name to check for
   * @return string Setting value from `.ini` file
   */
  private static function setting_exists($setting) {
    if(isset(self::$config[$setting])) {
      return self::$config[$setting];
    } else {
      throw new UnexpectedValueException("'$setting' is not a valid config setting. Please check your 'config.ini' file for valid config options.");
    }
  }

  /**
   * Set error reporting based on if app is in development or production
   *
   * @param null
   * @return null
   */
  public static function set_error_reporting() {
    if(self::get('development')) {
      ini_set('display_errors', '1');
      ini_set('error_reporting', E_ALL^E_NOTICE);
    } else {
      ini_set('display_errors', '0');
    }
  }

}

config::set_error_reporting();
require_once __DIR__ . '/vendor/autoload.php';
date_default_timezone_set(config::get('time_zone'));

?>
