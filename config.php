<?php
/**
  * App configuration
  *
  * @author Jared Howland <contacts@jaredhowland.com>
  * @version 2016-09-28
  * @since 2016-09-28
  *
  */

const CONFIG = 'config.ini';

class config {
  private static $config;

  public static function get($setting) {
    if(self::$config === null) {
      self::$config = parse_ini_file(CONFIG);
    }
    return self::setting_exists($setting);
  }

  private static function setting_exists($setting) {
    if(isset(self::$config[$setting])) {
      return self::$config[$setting];
    } else {
      throw new UnexpectedValueException("'$setting' is not a valid config setting. Please check your 'config.ini' file for valid config options.");
    }
  }

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
