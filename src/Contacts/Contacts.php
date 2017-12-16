<?php
/**
 * Methods Contacts between various contact classes
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @version 2017-12-11
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
     * @var string $dataDirectory Path to directory to save the vCard(s) to
     */
    private $dataDirectory;

    /**
     * @var string $defaultAreaCode String for default area code of phone numbers
     */
    protected $defaultAreaCode;

    /**
     * @var string $defaultTimeZone String for default time zone
     */
    protected $defaultTimeZone;

    /**
     * @var object $client Guzzle object for downloading files (photos, logos, etc.)
     */
    protected $client;

    /**
     * Construct
     *
     * @param string $dataDirectory   Directory to save vCard(s) to. Default: `/Data/`
     * @param string $defaultAreaCode Default area code to use for phone numbers without an area code. Default: `801`
     * @param string $defaultTimeZone Default time zone to use when adding a revision date to a vCard. Default: `America/Denver`
     *
     * @return void
     */
    protected function __construct(string $dataDirectory = null, string $defaultAreaCode = '801', string $defaultTimeZone = 'America/Denver')
    {
        $this->dataDirectory = empty($dataDirectory) ? Config::get('dataDirectory') : $dataDirectory;
        $this->defaultAreaCode = $defaultAreaCode;
        $this->defaultTimeZone = $defaultTimeZone;
        date_default_timezone_set($defaultTimeZone);
    }

    /**
     * Sanitize phone number
     *
     * @param string $phone Phone number
     *
     * @throws ContactsException if invalid phone number is used
     *
     * @return string|null Return formatted phone number if valid. Null otherwise.
     */
    protected function sanitizePhone(string $phone = null)
    {
        $phone = preg_replace("/[^0-9]/", '', $phone);
        if (strlen($phone) == 10) {
            $phone = sprintf("(%s) %s-%s", substr($phone, 0, 3), substr($phone, 3, 3), substr($phone, 6));

            return $phone;
        } elseif (strlen($phone) == 7) {
            $phone = sprintf("(".$this->defaultAreaCode.") %s-%s", substr($phone, 0, 3), substr($phone, 3));

            return $phone;
        } else {
            throw new ContactsException("Invalid phone: '$phone'");
        }
    }

    /**
     * Sanitize latitude and longitude
     *
     * @param string $lat  Geographic Positioning System latitude (decimal) (must be a number between -90 and 90)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     * @param string $long Geographic Positioning System longitude (decimal) (must be a number between -180 and 180)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     *
     * @throws ContactsException if invalid latitude or longitude is used
     *
     * @return array Array of sanitized latitude and longitude
     */
    protected function sanitizeLatLong(string $lat, string $long)
    {
        $latLong = $this->formatGeo($lat, $long);
        if (is_null($latLong['lat']) || is_null($latLong['long'])) {
            throw new ContactsException("Invalid latitude or longitude. Latitude: '$lat' Longitude: '$long'");
        } else {
            return $latLong;
        }
    }

    /**
     * Format latitude and longitude
     *
     * @param string $lat  Geographic Positioning System latitude (decimal) (must be a number between -90 and 90)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     * @param string $long Geographic Positioning System longitude (decimal) (must be a number between -180 and 180)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     *
     * @throws ContactsException if invalid latitude or longitude is used
     *
     * @return array Array of formatted latitude and longitude
     */
    protected function formatGeo(string $lat, string $long)
    {
        if (is_numeric($lat) && is_numeric($long)) {
            $lat = ($lat == 0) ? abs($lat) : $lat;
            $long = ($long == 0) ? abs($long) : $long;
            $lat = ($lat >= -90 && $lat <= 90) ? sprintf("%0.6f", round($lat, 6)) : null;
            $long = ($long >= -180 && $long <= 180) ? sprintf("%0.6f", round($long, 6)) : null;

            return ['lat' => $lat, 'long' => $long];
        } else {
            throw new ContactsException("Invalid latitude or longitude. Latitude: '$lat' Longitude: '$long'");
        }
    }

    /**
     * Sanitize email address
     *
     * @param string $email Email address
     *
     * @throws ContactsException if invalid email is used
     *
     * @return string|null Sanitized email address
     */
    protected function sanitizeEmail(string $email = null)
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        } else {
            throw new ContactsException("Invalid email: '$email'");
        }
    }

    /**
     * Sanitize time zone offset
     *
     * @param string $timeZone Time zone (UTC-offset) as a number between -14 and +12 (inclusive).
     *                         Examples: `-7`, `-07`, `-12`, `-12:00`, `10:30`
     *
     * @throws ContactsException if invalid time zone UTC offset is used
     *
     * @return array Time zone offset
     */
    protected function sanitizeTimeZone(string $timeZone)
    {
        $sign = ($timeZone[0] == '-') ? '-' : '+';
        $negative = ($sign === '-') ? '-' : null;
        $timeZone = preg_replace("/[^0-9:]/", '', $timeZone);
        $timeZone = ltrim($timeZone, '0');
        $offset = explode(':', $timeZone);
        $options = [];
        $options['options']['min_range'] = -14;
        $options['options']['max_range'] = 12;
        $hourOffset = filter_var($negative.$offset[0], FILTER_VALIDATE_INT, $options);
        if ($hourOffset) {
            $sign = abs($hourOffset) === 0 ? '+' : $sign;
            $minuteOffset = (isset($offset[1]) && $hourOffset) ? $offset[1] : '00';

            return [$sign, abs($hourOffset), $minuteOffset];
        } else {
            throw new ContactsException("Invalid time zone: '$timeZone'. UTC offset only. Text values not valid.");
        }
    }

    /**
     * Sanitize uniform resource locator (URL)
     *
     * @param string $url URL
     *
     * @throws ContactsException if invalid URL is used
     *
     * @return string|null Sanitized URL or `null`
     */
    protected function sanitizeUrl(string $url)
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        } else {
            throw new ContactsException("Invalid url: '$url'");
        }
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
    protected function inArrayAll(array $needles = [], array $haystack)
    {
        return !array_diff($needles, $haystack);
    }

    /**
     * Writes data to appropriate file
     *
     * @access protected
     *
     * @param string $fileName Name of file inside directory defined
     *                         in by `$this->dataDirectory`
     * @param string $data     String containing all data to write to file
     *                         Will overwrite any existing data
     * @param bool   $append   Whether or not to append data to end of file (overwrite is default). Default: `false`
     *
     * @throws ContactsException if file cannot be opened and/or written to
     *
     * @return void
     **/
    protected function writeFile(string $fileName, string $data, bool $append = false)
    {
        $rights = $append ? 'a' : 'w';
        $fileName = $this->dataDirectory.$fileName;
        if (!$handle = fopen($fileName, $rights)) {
            throw new ContactsException("Cannot open file '$fileName'");
        }
        if (fwrite($handle, $data) === false) {
            throw new ContactsException("Cannot write to file '$fileName'");
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
    protected function getData(string $url)
    {
        $this->client = new Client();
        $response = $this->client->get($url);

        return (string)$response->getBody();
    }
}
