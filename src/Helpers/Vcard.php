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
use GuzzleHttp\Exception\GuzzleException;

/**
 * Helper trait for methods shared between child classes
 */
trait Vcard
{
    use Generic;

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
     * Add the properties and return a string
     *
     * @param array $properties Properties to add to string
     * @return string String of properties
     */
    private function addProperties(array $properties): string
    {
        $string = null;
        foreach ($properties as $property) {
            $value = str_replace('\r\n', "\r\n", $property['value']);
            $string .= $this->fold($value . "\r\n");
        }

        return $string;
    }

    /**
     * Clean a string by escaping `,` and `;` and `:`
     *
     * @param array|string|null $string $string String to escape
     * @param string|null $delimiter Delimiter to create a list from an array. Default: `,`.
     *
     * @return string|null Returns cleaned string or `null`
     */
    private function cleanString(mixed $string, ?string $delimiter = ','): ?string
    {
        // If it's an array, clean individual strings and return a delimited list of array values
        if (is_array($string)) {
            foreach ($string as $key => $value) {
                $string[$key] = $this->cleanString($value, $delimiter);
            }

            return implode(/** @scrutinizer ignore-type */ $delimiter, $string);
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

    /**
     * Get the photo from a URL
     *
     * @param string $photoUrl URL of photo to grab
     *
     * @return array|null Info about photo in the URL or null if empty
     *
     * @throws ContactsException
     * @throws GuzzleException
     */
    private function getPhotoUrl(string $photoUrl): ?array
    {
        if (!empty($this->sanitizeUrl($photoUrl))) {
            $mimetype = strtoupper(str_replace('image/', '', getimagesize($photoUrl)['mime']));
            $photo = $this->getData($this->sanitizeUrl($photoUrl));
            return ['mimetype' => $mimetype, 'photo' => $photo];
        }

        return null;
    }

    /**
     * Get the base 64 version of a photo
     *
     * @param string $photoString Photo to convert to base 64
     * @return array|null Array with data or null if empty
     */
    private function getPhotoBase64(string $photoString): ?array
    {
        $img = base64_decode($photoString);
        if (!empty($img)) {
            $file = finfo_open();
            $mimetype = finfo_buffer($file, $img, FILEINFO_MIME_TYPE);
            $mimetype = strtoupper(str_replace('image/', '', $mimetype));

            return ['mimetype' => $mimetype, 'photoString' => $photoString];
        }

        return null;
    }
}
