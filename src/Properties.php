<?php

namespace Contacts;

use GuzzleHttp\Exception\GuzzleException;

use function in_array;
use function is_array;

/**
 * Properties class for `Vcard` class
 */
class Properties
{
    use Helpers\Vcard;

    /**
     * @var array $properties Array of properties added to the object
     */
    private array $properties = [];

    /**
     * @var array $definedElements Array of defined elements added to the object
     */
    private array $definedElements = [];

    /**
     * @var array $multiplePropertiesAllowed Array of properties that can be set more than once
     */
    private array $multiplePropertiesAllowed = [
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
    private array $validAddressTypes = [
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
    private array $validTelephoneTypes = [
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
        'iphone', /* Custom type for iOS and macOS applications */
    ];

    /**
     * @var array $validClassifications Array of valid classification types
     */
    private array $validClassifications = [
        'PUBLIC',
        'PRIVATE',
        'CONFIDENTIAL',
    ];

    /**
     * @var int $extendedItemCount Count of custom elements set
     */
    private int $extendedItemCount = 0;
    private Options $options;

    /**
     * Construct Vcard Class
     *
     * @param Options|null $options
     */
    public function __construct(Options $options = null)
    {
        $this->options = $options ?? new Options();
    }

    /**
     * Get all defined properties
     *
     * @return array Array of all properties
     */
    public function get(): array
    {
        return $this->properties;
    }

    /**
     * Get valid address types
     *
     * @return array|string[]
     */
    public function getValidAddressTypes(): array
    {
        return $this->validAddressTypes;
    }

    /**
     * Get valid phone types
     *
     * @return array|string[]
     */
    public function getValidTelephoneTypes(): array
    {
        return $this->validTelephoneTypes;
    }

    /**
     * Get valid classifications
     *
     * @return array|string[]
     */
    public function getValidClassifications(): array
    {
        return $this->validClassifications;
    }

    /**
     * Add photo to `PHOTO` or `LOGO` elements
     *
     * @param string $element Element to add photo to
     * @param string $photo   URL-referenced or base-64 encoded photo
     * @param bool   $isUrl   Optional. Is it a URL-referenced photo or a base-64 encoded photo? Default: `true`
     *
     * @throws ContactsException
     * @throws GuzzleException
     */
    public function photoProperty(string $element, string $photo, bool $isUrl = true): void
    {
        if ($isUrl) {
            $this->photoURL($element, $photo);
        } else {
            $this->photoBase64($element, $photo);
        }
    }

    /**
     * Set a defined element
     *
     * @param string $element
     *
     * @return $this
     */
    public function setDefinedElements(string $element): Properties
    {
        $this->definedElements[$element] = true;

        return $this;
    }

    /**
     * Get defined elements array
     *
     * @return array Array of defined elements
     */
    public function getDefinedElements(): array
    {
        return $this->definedElements;
    }

    /**
     * Return extended item count
     *
     * @return int Count of extended item elements
     */
    public function getExtendedItemCount(): int
    {
        $this->extendedItemCount++;

        return $this->extendedItemCount;
    }

    /**
     * Construct the element
     *
     * @param string       $element   Name of the vCard element
     * @param array|string $value     Value to construct. If it's an array, make it a list using the proper `delimiter`
     * @param string       $delimiter Delimiter to use for lists given via `$value` array.
     *                                Default: `,`.
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function constructElement(string $element, mixed $value, string $delimiter = ','): void
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
     * Set property
     *
     * @param string $element Element to set
     * @param string $value   Value to set element to
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function setProperty(string $element, string $value): void
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

    /**
     * Add the properties and return a string
     *
     * @param array $properties Properties to add to string
     *
     * @return string String of properties
     */
    public function addProperties(array $properties): string
    {
        $string = null;
        foreach ($properties as $property) {
            $value  = str_replace('\r\n', "\r\n", $property['value']);
            $string .= $this->fold($value."\r\n");
        }

        return $string;
    }

    /**
     * Add photo to `PHOTO` or `LOGO` elements
     *
     * @param string $element  Element to add photo to
     * @param string $photoUrl URL-referenced or base-64 encoded photo
     *
     * @throws ContactsException|GuzzleException if an element that can only be defined once is defined more than once
     */
    private function photoURL(string $element, string $photoUrl): void
    {
        // Set directly rather than going through $this->properties->constructElement to avoid escaping valid URL characters
        $data = $this->getPhotoUrl($photoUrl);
        if ($data !== null) {
            $this->setProperty(
                $element,
                vsprintf(Config::get('PHOTO-BINARY'), [$data['mimetype'], base64_encode($data['photo'])])
            );
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
    private function photoBase64(string $element, string $photoString): void
    {
        $data = $this->getPhotoBase64($photoString);
        if ($data !== null) {
            $this->setProperty(
                $element,
                vsprintf(Config::get('PHOTO-BINARY'), [$data['mimetype'], $data['photoString']])
            );
        }
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
            $mimetype = $this->getImageMimeType($photoUrl);
            $mimetype = strtoupper(str_replace('image/', '', $mimetype));
            $photo    = $this->getData($this->sanitizeUrl($photoUrl));

            return ['mimetype' => $mimetype, 'photo' => $photo];
        }

        return null;
    }

    /**
     * Get the mime type of image
     *
     * @param string $image Image to get mime type for
     *
     * @return string Mime type of image
     */
    private function getImageMimeType(string $image): string
    {
        return image_type_to_mime_type(exif_imagetype($image));
    }

    /**
     * Get the base 64 version of a photo
     *
     * @param string $photoString Photo to convert to base 64
     *
     * @return array|null Array with data or null if empty
     */
    private function getPhotoBase64(string $photoString): ?array
    {
        $img = base64_decode($photoString);
        if (!empty($img)) {
            $file     = finfo_open();
            $mimetype = finfo_buffer($file, $img, FILEINFO_MIME_TYPE);
            $mimetype = strtoupper(str_replace('image/', '', $mimetype));

            return ['mimetype' => $mimetype, 'photoString' => $photoString];
        }

        return null;
    }
}
