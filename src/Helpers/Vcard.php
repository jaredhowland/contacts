<?php
/**
 * vCard helper class
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @version 2020-02-28
 * @since   2020-02-28
 */

namespace Contacts\Helpers;

use Contacts\Config;
use Contacts\ContactsException;

/**
 * vCard class to create a vCard. Extends `Contacts` and implements `ContactsInterface`
 */
trait Vcard
{
    use Generic;

    /**
     * @var array $properties Array of properties added to the vCard object
     */
    protected $properties;

    /**
     * @var array $multiplePropertiesAllowed Array of properties that can be set more than once
     */
    protected $multiplePropertiesAllowed = [
        'EMAIL',
        'ADR',
        'LABEL',
        'TEL',
        'EMAIL',
        'URL',
        'X-',
        'CHILD',
    ];

    /**
     * @var array $validAddressTypes Array of valid address types
     */
    protected $validAddressTypes = [
        'dom',
        'intl',
        'postal',
        'parcel',
        'home',
        'work',
        'pref',
    ];

    /**
     * @var array $validTelephoneTypes Array of valid telephone types
     */
    protected $validTelephoneTypes = [
        'home',
        'msg',
        'work',
        'pref',
        'voice',
        'fax',
        'cell',
        'video',
        'pager',
        'bbs',
        'modem',
        'car',
        'isdn',
        'pcs',
        'iphone',
    ]; // Custom type for iOS and macOS applications

    /**
     * @var array $validClassifications Array of valid classification types
     */
    protected $validClassifications = [
        'PUBLIC',
        'PRIVATE',
        'CONFIDENTIAL',
    ];

    /**
     * @var int $extendedItemCount Count of custom iOS elements set
     */
    protected $extendedItemCount = 1;

    /**
     * @var array $definedElements Array of defined vCard elements added to the vCard object
     */
    protected $definedElements;

    /**
     * Set filename
     *
     * @param string|null $filename Name of file. Default: current date and time
     *
     * @return string Name of file
     */
    protected function setFilename(string $filename = null): string
    {
        return $filename ?? (string)date('Y.m.d.H.i.s');
    }

    /**
     * Set revision date
     */
    protected function setRevisionDate(): void
    {
        if (!isset($this->definedElements['REV'])) {
            $this->addRevision();
        }
    }

    /**
     * Set vCard string
     *
     * @return string vCard string
     */
    protected function setVcardString(): string
    {
        $string = "BEGIN:VCARD\r\n";
        $string .= "VERSION:3.0\r\n";
        foreach ($this->properties as $property) {
            $value = str_replace('\r\n', "\r\n", $property['value']);
            $string .= $this->fold($value."\r\n");
        }
        $string .= "END:VCARD\r\n\r\n";

        return $string;
    }

    /**
     * Fold vCard text so each line is 75 characters or less
     *
     * RFC 2426 p. 7
     *
     * @link https://tools.ietf.org/html/rfc2426#section-2.6 RFC 2426 Section 2.6 (p. 7)
     *
     * @param string $text Text to fold
     *
     * @return string Folded text
     */
    protected function fold(string $text): string
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
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    protected function photoProperty(string $element, string $photo, bool $isUrl = true): self
    {
        $isUrl ? $this->photoUrl($element, $photo) : $this->photoBase64($element, $photo);

        return $this;
    }

    /**
     * Add photo to `PHOTO` or `LOGO` elements
     *
     * @param string $element  Element to add photo to
     * @param string $photoUrl URL-referenced or base-64 encoded photo
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    protected function photoUrl(string $element, string $photoUrl): void
    {
        // Set directly rather than going through $this->constructElement to avoid escaping valid URL characters
        if ($this->sanitizeUrl($photoUrl)) {
            $mimetype = strtoupper(str_replace('image/', '', getimagesize($photoUrl)['mime']));
            $photo    = $this->getData($photoUrl);
            $this->setProperty($element, vsprintf(Config::get('PHOTO-BINARY'), [$mimetype, base64_encode($photo)]));
        }
    }

    /**
     * Add photo to `PHOTO` or `LOGO` elements
     *
     * @param string $element     Element to add photo to
     * @param string $photoString URL-referenced or base-64 encoded photo
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    protected function photoBase64(string $element, string $photoString): void
    {
        $img = base64_decode($photoString);
        if (!empty($img)) {
            $file     = finfo_open();
            $mimetype = finfo_buffer($file, $img, FILEINFO_MIME_TYPE);
            $mimetype = strtoupper(str_replace('image/', '', $mimetype));
            $this->setProperty($element, vsprintf(Config::get('PHOTO-BINARY'), [$mimetype, $photoString]));
        }
    }

    /**
     * Construct the element
     *
     * @param string       $element   Name of the vCard element
     * @param string|array $value     Value to construct. If it's an array, make it a list using the proper `delimiter`
     * @param string       $delimiter Delimiter to use for lists given via `$value` array.
     *                                Default: `,`.
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    protected function constructElement(string $element, $value, string $delimiter = ','): void
    {
        $value = is_array($value) ? array_map(
            [$this, 'cleanString'],
            $value,
            [$delimiter]
        ) : [$this->cleanString($value)];
        if (!empty($value) && !empty(Config::get($element))) {
            $this->setProperty($element, vsprintf(Config::get($element), $value));
        }
    }

    /**
     * Clean a string by escaping `,` and `;` and `:`
     *
     * @param string|array $string    String to escape
     * @param string       $delimiter Delimiter to create a list from an array. Default: `,`.
     *
     * @return string|null Returns cleaned string or `null`
     */
    protected function cleanString($string, $delimiter = ','): ?string
    {
        // If it's an array, clean individual strings and return a delimited list of array values
        if (is_array($string)) {
            foreach ($string as $key => $value) {
                $string[$key] = $this->cleanString($value, $delimiter);
            }

            return implode($delimiter, $string);
        }
        $search  = [',', ';', ':'];
        $replace = ['\,', '\;', '\:'];

        return empty($string) ? null : str_replace($search, $replace, $string);
    }

    /**
     * Set vCard property
     *
     * @param string $element vCard element to set
     * @param string $value   Value to set vCard element to
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    protected function setProperty(string $element, string $value): void
    {
        if (isset($this->definedElements[$element]) && !in_array($element, $this->multiplePropertiesAllowed, true)) {
            throw new ContactsException('You can only set "'.$element.'" once.');
        }
        // Define that we set this element
        $this->definedElements[$element] = true;
        // Add property
        $this->properties[] = [
            'key' => $element,
            'value' => $value,
        ];
    }
}
