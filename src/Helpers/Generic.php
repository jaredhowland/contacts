<?php
/**
 * Share Contacts methods between classes
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @version 2020-01-24
 * @since   2016-10-05
 *
 */

namespace Contacts\Helpers;

use Contacts\Config;
use Contacts\ContactsException;
use GuzzleHttp\Client;

/**
 * Helper trait for methods shared between child classes
 */
trait Generic
{
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
     * @var string $dataDirectory Path to directory to save the vCard(s) to
     */
    private $dataDirectory;

    /**
     * Setup Helper trait
     *
     * @param string $dataDirectory   Directory to save vCard(s) to. Default: `Config::get('dataDirectory')` value
     * @param string $defaultAreaCode Default area code to use for phone numbers without an area code. Default: `801`
     * @param string $defaultTimeZone Default time zone to use when adding a revision date to a vCard. Default:
     *                                `America/Denver`
     */
    protected function setup(
        string $dataDirectory = null,
        string $defaultAreaCode = '801',
        string $defaultTimeZone = 'America/Denver'
    ): void {
        $this->dataDirectory   = empty($dataDirectory) ? Config::get('dataDirectory') : $dataDirectory;
        $this->defaultAreaCode = $defaultAreaCode;
        $this->defaultTimeZone = $defaultTimeZone;
        date_default_timezone_set($defaultTimeZone);
    }

    /**
     * Sanitize phone number
     *
     * @param string $phone Phone number
     *
     * @return string|null Return formatted phone number if valid. Null otherwise.
     *
     * @throws ContactsException if invalid phone number is used
     */
    protected function sanitizePhone(string $phone = null): ?string
    {
        $phone = preg_replace('/[\D]/', '', $phone);
        if (strlen($phone) === 10) {
            $phone = sprintf('(%s) %s-%s', substr($phone, 0, 3), substr($phone, 3, 3), substr($phone, 6));

            return $phone;
        }

        if (strlen($phone) === 7) {
            $phone = sprintf('('.$this->defaultAreaCode.') %s-%s', substr($phone, 0, 3), substr($phone, 3));

            return $phone;
        }

        throw new ContactsException("Invalid phone: '$phone'");
    }

    /**
     * Sanitize latitude and longitude
     *
     * @param float $lat  Geographic Positioning System latitude (decimal) (must be a number between -90 and 90)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     * @param float $long Geographic Positioning System longitude (decimal) (must be a number between -180 and 180)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     *
     * @return array Array of sanitized latitude and longitude
     *
     * @throws ContactsException if invalid latitude or longitude is used
     */
    protected function sanitizeLatLong(float $lat, float $long): array
    {
        $latLong = $this->cleanLatLong($lat, $long);
        if ($latLong['lat'] === null || $latLong['long'] === null) {
            throw new ContactsException("Invalid latitude or longitude. Latitude: '$lat' Longitude: '$long'");
        }

        return $latLong;
    }

    /**
     * Sanitize email address
     *
     * @param string $email Email address
     *
     * @return string|null Sanitized email address
     *
     * @throws ContactsException if invalid email is used
     */
    protected function sanitizeEmail(string $email = null): ?string
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return filter_var($email, FILTER_SANITIZE_EMAIL);
        }

        throw new ContactsException("Invalid email: '$email'");
    }

    /**
     * Sanitize time zone offset
     *
     * @param string $timeZone Time zone (UTC-offset) as a number between -14 and +12 (inclusive).
     *                         Examples: `-7`, `-07`, `-12`, `-12:00`, `10:30`
     *
     * @return array Time zone offset
     *
     * @throws ContactsException if invalid time zone UTC offset is used
     */
    protected function sanitizeTimeZone(string $timeZone): array
    {
        $prefix   = $this->getPrefixes($timeZone);
        $timeZone = $prefix['negative'].$this->cleanTimeZone($timeZone);
        if ($this->getTimeZoneOffset($timeZone)['hourOffset']) {
            return [
                $prefix['sign'],
                abs($this->getTimeZoneOffset($timeZone)['hourOffset']),
                $this->getTimeZoneOffset($timeZone)['minuteOffset'],
            ];
        }

        throw new ContactsException("Invalid time zone: '$timeZone'. UTC offset only. Text values not valid.");
    }

    /**
     * Sanitize uniform resource locator (URL)
     *
     * @param string $url URL
     *
     * @return string|bool Sanitized URL or `false` if not a valid URL
     *
     * @throws ContactsException if invalid URL is used
     */
    protected function sanitizeUrl(string $url): ?string
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return filter_var($url, FILTER_SANITIZE_URL);
        }

        throw new ContactsException("Invalid url: '$url'");
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
    protected function inArrayAll(array $needles, array $haystack): bool
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
     */
    protected function writeFile(string $fileName, string $data, bool $append = false): void
    {
        $rights   = $append ? 'a' : 'w';
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
    protected function getData(string $url): string
    {
        $this->client = new Client();
        $response     = $this->client->get($url);

        return (string)$response->getBody();
    }

    /**
     * Clean latitude and longitude
     *
     * @param mixed $lat  Geographic Positioning System latitude (decimal) (must be a number between -90 and 90)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     * @param mixed $long Geographic Positioning System longitude (decimal) (must be a number between -180 and 180)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     *
     * @return array Array of formatted latitude and longitude
     */
    private function cleanLatLong($lat, float $long): array
    {
        $lat  = $this->constrainLatLong($lat, 90, -90);
        $long = $this->constrainLatLong($long, 180, -180);

        return ['lat' => $lat, 'long' => $long];
    }

    /**
     * Constrain latitude and longitude
     *
     * @param float $float Latitude or longitude value
     * @param int   $max    Max value for latitude or longitude
     * @param int   $min    Min value for latitude or longitude
     *
     * @return string|null Latitude or longitude rounded to 6 decimal places. Default if invalid: `null`
     */
    private function constrainLatLong(float $float, int $max, int $min): ?string
    {
        return ($float >= $min && $float <= $max) ? sprintf('%1.6f', round($float, 6)) : null;
    }

    /**
     * Gets time zone prefixes
     *
     * @param string $timeZone Time zone (UTC-offset) as a number between -14 and +12 (inclusive).
     *                         Examples: `-7`, `-07`, `-12`, `-12:00`, `10:30`
     *
     * @return array Time zone prefixes
     */
    private function getPrefixes(string $timeZone): array
    {
        $sign     = (strpos($timeZone, '-') === 0 && $timeZone[0] !== 0) ? '-' : '+';
        $negative = strpos($timeZone, '-') === 0 ? '-' : null;

        return ['sign' => $sign, 'negative' => $negative];
    }

    /**
     * Strip the time zone of all characters except numbers and `:`; trim leading `0`s
     *
     * @param string $timeZone Time zone (UTC-offset) as a number between -14 and +12 (inclusive).
     *                         Examples: `-7`, `-07`, `-12`, `-12:00`, `10:30`
     *
     * @return string Cleaned time zone string
     */
    private function cleanTimeZone(string $timeZone): string
    {
        $timeZone = preg_replace('/[^0-9:]/', '', $timeZone);

        return ltrim($timeZone, '0');
    }

    /**
     * Get the time zone offset
     *
     * @param string $timeZone Time zone (UTC-offset) as a number between -14 and +12 (inclusive).
     *                         Examples: `-7`, `-07`, `-12`, `-12:00`, `10:30`
     *
     * @return array Time zone offsets for hour and minute
     */
    private function getTimeZoneOffset(string $timeZone): array
    {
        $offset       = explode(':', $timeZone);
        $hourOffset   = filter_var(
            $offset[0],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => -14, 'max_range' => 12]]
        );
        $minuteOffset = $offset[1] ?? '00';

        return ['hourOffset' => $hourOffset, 'minuteOffset' => $minuteOffset];
    }
}
