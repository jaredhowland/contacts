<?php
/**
 * Methods Contacts between various contact classes
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @version 2017-12-05
 * @since   2016-10-05
 *
 */

namespace Contacts;

use GuzzleHttp\Client;

/**
 * Contacts class for methods shared between child classes
 */
class Contacts
{
    /**
     * @var object $client Guzzle object for downloading files (photos, logos, etc.)
     */
    protected $client;

    /**
     * Sanitize phone number
     *
     * @param int $phone Phone number (numbers only)
     *
     * @return int|null Return phone number if valid. Null otherwise.
     */
    protected function sanitizePhone($phone)
    {
        $phone = preg_replace("/[^0-9]/", '', $phone);
        if (strlen($phone) == 10) {
            $phone = sprintf("(%s) %s-%s", substr($phone, 0, 3), substr($phone, 3, 3), substr($phone, 6));

            return $phone;
        } elseif (strlen($phone) == 7) {
            $phone = sprintf('('.\contacts\config::get('defaultAreaCode').") %s-%s", substr($phone, 0, 3),
                substr($phone, 3));

            return $phone;
        } else {
            return null;
        }
    }

    /**
     * Sanitize latitude and longitude
     *
     * @param string $lat  Geographic Positioning System latitude (decimal) (must be a number between -90 and 90)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     * @param string $long Geopgraphic Positioning System longitude (decimal) (must be a number between -180 and 180)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     *
     * @return array Array of sanitized latitude and longitude
     */
    protected function sanitizeLatLong($lat, $long)
    {
        $lat = ($lat >= -90 && $lat <= 90) ? sprintf("%0.6f", round($lat, 6)) : '0.000000';
        $long = ($long >= -180 && $long <= 180) ? sprintf("%0.6f", round($long, 6)) : '0.000000';

        return array($lat, $long);
    }

    /**
     * Sanitize email address
     *
     * @param string $email Email address
     *
     * @return string Sanitized email address
     */
    protected function sanitizeEmail($email)
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * Sanitize time zone offset
     *
     * @param string $timeZone Time zone (UTC-offset) as a number between -14 and +12 (inclusive - do not zero-pad).
     *                         Examples: `-7`, `-12`, `-12:00`, `10:30`
     *
     * @return array Time zone offset
     */
    protected function sanitizeTimeZone($timeZone)
    {
        $offset = explode(':', $timeZone);
        $options['options']['minRange'] = -14;
        $options['options']['maxRange'] = 12;
        $hourOffset = filter_var($offset[0], FILTER_VALIDATE_INT, $options);
        $sign = ($hourOffset < 0) ? '-' : '+';
        $minuteOffset = $offset[1] ? $offset[1] : '00';

        return array($sign, abs($hourOffset), $minuteOffset);
    }

    /**
     * Sanitize uniform resource locator (URL)
     *
     * @param string $url URL
     *
     * @return string Sanitized URL
     */
    protected function sanitizeUrl($url)
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    /**
     * Checks to see if ALL values of an array appear in different array
     *
     * @link http://stackoverflow.com/questions/7542694/in-array-multiple-values/11040612#11040612
     *
     * @param array $needles  Strings to look for in the $haystack array
     * @param array $haystack Strings to be searched by $needles
     *
     * @return bool TRUE if all appear. FALSE otherwise.
     */
    protected function inArrayAll($needles, $haystack)
    {
        return !array_diff($needles, $haystack);
    }

    /**
     * Writes data to appropriate file
     *
     * @access protected
     *
     * @param string $fileName Name of file inside directory defined
     *                         in the config file ('dataDirectory')
     * @param string $data     String containing all data to write to file
     *                         Will overwrite any existing data
     * @param bool   $append   Whether or not to append data to end of file (overwrite is default)
     *
     * @return null
     **/
    protected function writeFile($fileName, $data, $append = false)
    {
        $rights = $append ? 'a' : 'w';
        $fileName = '.'.\contacts\config::get('dataDirectory').$fileName;
        if (!$handle = fopen($fileName, $rights)) {
            echo "Cannot open file '$fileName'";
            exit;
        }
        if (fwrite($handle, $data) === false) {
            echo "Cannot write to file '$fileName'";
            exit;
        }
        fclose($handle);
    }

    /**
     * Gets data using Guzzle
     *
     * @param string $url URL of data to grab
     *
     * @return string Contents of the passed URL
     */
    protected function getData($url)
    {
        $this->client = new Client();
        $response = $this->client->get($url);

        return (string)$response->getBody();
    }
}

?>
