<?php
/**
 * Helper methods for `Vcard` class
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @version 2023-09-05
 * @since   2023-09-05
 *
 */

namespace Contacts\Helpers;

use Contacts\ContactsException;
use Contacts\Config;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Helper trait for methods shared between child classes
 */
trait Vcard
{
    /**
     * Fold vCard text so each line is 75 characters or fewer
     *
     * RFC 2426 p. 7
     *
     * @link https://tools.ietf.org/html/rfc2426#section-2.6 RFC 2426 Section 2.6 (p. 7)
     *
     * @param string $text Text to fold
     *
     * @return string Folded text
     */
    private function fold(string $text): string
    {
        return (strlen($text) <= 75) ? $text : substr(chunk_split($text, 73, "\r\n "), 0, -3);
    }

    /**
     * Clean a string by escaping `,` and `;` and `:`
     *
     * @param array|string|null $string $string String to escape
     * @param string|null $delimiter Delimiter to create a list from an array. Default: `,`.
     *
     * @return string|null Returns cleaned string or `null`
     */
    private function cleanString(array|string|null $string, ?string $delimiter = ','): ?string
    {
        // If it's an array, clean individual strings and return a delimited list of array values
        if (is_array($string)) {
            foreach ($string as $key => $value) {
                $string[$key] = $this->cleanString($value, $delimiter);
            }

            return implode($delimiter, $string);
        }
        $search = [',', ';', ':'];
        $replace = ['\,', '\;', '\:'];

        return empty($string) ? null : str_replace($search, $replace, $string);
    }

    /**
     * Remove spaces from comma-delimited list
     *
     * @param string|null $list List to remove spaces from
     * @return string Cleaned or empty string
     */
    private function removeSpacesFromList(?string $list): string
    {
        return str_replace(search: ', ', replace: ',', subject: $list ?? '');
    }
}
