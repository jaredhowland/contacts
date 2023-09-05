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
     * Add photo to `PHOTO` or `LOGO` elements
     *
     * @param string $element Element to add photo to
     * @param string $photo   URL-referenced or base-64 encoded photo
     * @param bool   $isUrl   Optional. Is it a URL-referenced photo or a base-64 encoded photo? Default: `true`
     *
     * @return $this
     *
     * @throws ContactsException
     * @throws GuzzleException
     */
    private function photoProperty(string $element, string $photo, bool $isUrl = true): self
    {
        if ($isUrl) {
            $this->photoURL($element, $photo);
        } else {
            $this->photoBase64($element, $photo);
        }

        return $this;
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
}
