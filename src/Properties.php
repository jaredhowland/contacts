<?php

namespace Contacts;

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

    /**
     * Get all defined properties
     *
     * @return array Array of all properties
     */
    public function get(): array
    {
        return $this->properties;
    }

    public function getValidAddressTypes(): array
    {
        return $this->validAddressTypes;
    }

    public function getValidTelephoneTypes(): array
    {
        return $this->validTelephoneTypes;
    }

    public function getValidClassifications(): array
    {
        return $this->validClassifications;
    }

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
     * @param string $element Name of the vCard element
     * @param array|string $value Value to construct. If it's an array, make it a list using the proper `delimiter`
     * @param string $delimiter Delimiter to use for lists given via `$value` array.
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
     * @param string $value Value to set element to
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function setProperty(string $element, string $value): void
    {
        if (isset($this->definedElements[$element]) && !in_array($element, $this->multiplePropertiesAllowed, true)) {
            throw new ContactsException('You can only set "' . $element . '" once.');
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
     * @return string String of properties
     */
    public function addProperties(array $properties): string
    {
        $string = null;
        foreach ($properties as $property) {
            $value = str_replace('\r\n', "\r\n", $property['value']);
            $string .= $this->fold($value . "\r\n");
        }

        return $string;
    }
}