<?php
/**
 * Share Contacts methods between classes
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @version 2023-09-03
 * @since   2016-10-05
 *
 */

namespace Contacts;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Helper trait for methods shared between child classes
 */
trait Helpers
{
    /**
     * @var object $client Guzzle object for downloading files (photos, logos, etc.)
     */
    protected object $client;

    /**
     * Sanitize latitude and longitude
     *
     * @param float $lat Geographic Positioning System latitude (decimal) (must be a number between -90 and 90)
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
        $latLong = $this->formatGeo($lat, $long);
        if ($latLong['lat'] === null || $latLong['long'] === null) {
            throw new ContactsException("Invalid latitude or longitude. Latitude: '$lat' Longitude: '$long'");
        }

        return $latLong;
    }

    /**
     * Format latitude and longitude
     *
     * @param float $lat Geographic Positioning System latitude (decimal) (must be a number between -90 and 90)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     * @param float $long Geographic Positioning System longitude (decimal) (must be a number between -180 and 180)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     *
     * @return array Array of formatted latitude and longitude
     */
    protected function formatGeo(float $lat, float $long): array
    {
        return $this->cleanLatLong($lat, $long);
    }

    /**
     * Sanitize email address
     *
     * @param string|null $email Email address
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
        $prefix = $this->getPrefixes($timeZone);
        $timeZone = $prefix['negative'] . $this->cleanTimeZone($timeZone);
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
     * @return string|null Sanitized URL or `null`
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
     * @param array $needles Strings to look for in the $haystack array
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
     * @param string $data String containing all data to write to file
     *                         Will overwrite any existing data
     * @param bool $append Whether to append data to end of file (overwrite is default). Default: `false`
     *
     * @throws ContactsException if file cannot be opened and/or written to
     */
    protected function writeFile(string $fileName, string $data, bool $append = false): void
    {
        $rights = $append ? 'a' : 'w';
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
     * @throws GuzzleException
     */
    protected function getData(string $url): string
    {
        $this->client = new Client();
        $response = $this->client->get($url);

        return (string)$response->getBody();
    }

    /**
     * Clean latitude and longitude
     *
     * @param float $lat Geographic Positioning System latitude (decimal) (must be a number between -90 and 90)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     * @param float $long Geographic Positioning System longitude (decimal) (must be a number between -180 and 180)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     *
     * @return array Array of formatted latitude and longitude
     */
    private function cleanLatLong(float $lat, float $long): array
    {
        $lat = $this->constrainLatLong($lat, 90, -90);
        $long = $this->constrainLatLong($long, 180, -180);

        return ['lat' => $lat, 'long' => $long];
    }

    /**
     * Constrain latitude and longitude
     *
     * @param float $string Latitude or longitude value
     * @param int $max Max value for latitude or longitude
     * @param int $min Min value for latitude or longitude
     *
     * @return string|null Latitude or longitude rounded to 6 decimal places. Default if invalid: `null`
     */
    private function constrainLatLong(float $string, int $max, int $min): ?string
    {
        return ($string >= $min && $string <= $max) ? sprintf('%1.6f', round($string, 6)) : null;
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
        $sign = (str_starts_with($timeZone, '-') && $timeZone[0] != 0) ? '-' : '+';
        $negative = str_starts_with($timeZone, '-') ? '-' : null;

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
        $offset = explode(':', $timeZone);
        $hourOffset = filter_var(
            $offset[0],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => -14, 'max_range' => 12]]
        );
        $minuteOffset = $offset[1] ?? '00';

        return ['hourOffset' => $hourOffset, 'minuteOffset' => $minuteOffset];
    }
}
