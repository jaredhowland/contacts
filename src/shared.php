<?php
/**
  * Methods shared between various contact classes
  *
  * @author Jared Howland <contacts@jaredhowland.com>
  * @version 2016-10-19
  * @since 2016-10-05
  *
  */

namespace contacts;

trait shared {

  /**
   * Sanitize phone number
   *
   * @param int $phone Phone number (numbers only)
   * @return int|null Return phone number if valid. Null otherwise.
   */
  protected function sanitize_phone($phone) {
    $phone = preg_replace("/[^0-9]/", '', $phone);
    if(strlen($phone) == 10) {
      $phone = sprintf("(%s) %s-%s", substr($phone, 0, 3), substr($phone, 3, 3), substr($phone, 6));
      return $phone;
    } elseif(strlen($phone) == 7) {
      $phone = sprintf('(' . config::get('default_area_code') . ") %s-%s", substr($phone, 0, 3), substr($phone, 3));
      return $phone;
    } else {
      return null;
    }
  }

  /**
   * Sanitize latitude and longitude
   *
   * @param string $lat Geographic Positioning System latitude (decimal) (must be a number between -90 and 90)
   *
   * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
   * @param string $long Geopgraphic Positioning System longitude (decimal) (must be a number between -180 and 180)
   *
   * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
   * @return array Array of sanitized latitude and longitude
   */
  protected function sanitize_lat_long($lat, $long) {
    $lat = ($lat >=-90 && $lat <=90) ? sprintf("%0.6f", round($lat, 6)) : '0.000000';
    $long = ($long >=-180 && $long <=180) ? sprintf("%0.6f", round($long, 6)) : '0.000000';
    return array($lat, $long);
  }

  /**
   * Sanitize email address
   *
   * @param string $email Email address
   * @return string Sanitized email address
   */
  protected function sanitize_email($email) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
  }

  /**
   * Sanitize time zone offset
   *
   * @param string $time_zone Time zone (UTC-offset) as a number between -14 and +12 (inclusive - do not zero-pad). Examples: `-7`, `-12`, `-12:00`, `10:30`
   * @return array Time zone offset
   */
  protected function sanitize_time_zone($time_zone) {
    $offset = explode(':', $time_zone);
    $options['options']['min_range'] = -14;
    $options['options']['max_range'] = 12;
    $hour_offset = filter_var($offset[0], FILTER_VALIDATE_INT, $options);
    $sign = ($hour_offset < 0) ? '-' : '+';
    $minute_offset = $offset[1] ? $offset[1] : '00';
    return array($sign, abs($hour_offset), $minute_offset);
  }

  /**
   * Sanitize uniform resource locator (URL)
   *
   * @param string $url URL
   * @return string Sanitized URL
   */
  protected function sanitize_url($url) {
    $url = filter_var($url, FILTER_SANITIZE_URL);
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
  }

  /**
   * Checks to see if ALL values of an array appear in different array
   *
   * @link http://stackoverflow.com/questions/7542694/in-array-multiple-values/11040612#11040612
   * @param array $needles Strings to look for in the $haystack array
   * @param array $haystack Strings to be searched by $needles
   * @return bool TRUE if all appear. FALSE otherwise.
   */
  protected function in_array_all($needles, $haystack) {
    return !array_diff($needles, $haystack);
  }

  /**
   * Writes data to appropriate file
   *
   * @access protected
   * @param string $file_name Name of file inside directory defined
   *                          in the config file ('data_directory')
   * @param string $data String containing all data to write to file
   *                     Will overwrite any existing data
   * @return null
   **/
  protected function write_file($file_name, $data, $append = false) {
    $rights = $append ? 'a' : 'w';
    $file_name = '.' . config::get('data_directory') . $file_name;
    if(!$handle = fopen($file_name, $rights)) {
      echo "Cannot open file '$file_name'";
      exit;
    }
    if(fwrite($handle, $data) === FALSE) {
      echo "Cannot write to file '$file_name'";
      exit;
    }
    fclose($handle);
  }
}

?>
