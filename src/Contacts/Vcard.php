<?php
/**
 * Create a vCard
 *
 * Known issues:
 *   * Date-time values not supported for `BDAY` field (only date values). No plans to implement.
 *   * Text values not supported for `TZ` field (only UTC-offset values). No plans to implement.
 *   * Binary photo data not supported for `LOGO` field (URL-referenced values only). No plans to implement.
 *   * The following vCard elements are not currently supported (no plans to implement):
 *     * AGENT
 *     * SOUND
 *     * KEY
 *
 * Inspired by https://github.com/jeroendesloovere/vcard
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @version 2017-12-05
 * @since   2016-10-05
 *
 */

namespace Contacts;

/**
 * vCard class to create a vCard
 */
class Vcard extends Contacts implements ContactInterface
{
    /**
     * @var array $properties Array of properties added to the vCard object
     */
    private $properties;

    /**
     * @var array $multiplePropertiesAllowed Array of properties that can be set more than once
     */
    private $multiplePropertiesAllowed = array(
        'EMAIL',
        'ADR',
        'LABEL',
        'TEL',
        'EMAIL',
        'URL',
        'X-',
        'CHILD',
    );

    /**
     * @var array $validAddressTypes Array of valid address types
     */
    private $validAddressTypes = array(
        'dom',
        'intl',
        'postal',
        'parcel',
        'home',
        'work',
        'pref',
    );

    /**
     * @var array $validTelephoneTypes Array of valid telephone types
     */
    private $validTelephoneTypes = array(
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
        'iphone' // Custom type
    );

    /**
     * @var array $validClassifications Array of valid classification types
     */
    private $validClassifications = array(
        'PUBLIC',
        'PRIVATE',
        'CONFIDENTIAL',
    );

    /**
     * @var int $extendedItemCount Count of custom iOS elements set
     */
    private $extendedItemCount = 1;

    /**
     * @var array $definedElements Array of defined vCard elements added to the vCard object
     */
    private $definedElements;

    /**
     * Print out properties and define elements to help with debugging
     *
     * @param null
     *
     * @return null
     */
    public function debug()
    {
        echo "<pre>**PROPERTIES**\n";
        print_r($this->properties);
        echo "\n\n**DEFINED ELEMENTS**\n";
        print_r($this->definedElements);
    }

    /**
     * Add full name to vCard
     *
     * RFC 2426 pp. 7–8
     *
     * This type is based on the semantics of the X.520
     * Common Name attribute. The property MUST be present in the vCard
     * object.
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.1.1 RFC 2426 Section 3.1.1 (pp. 7-8)
     *
     * @param string $name Full name
     *
     * @return null
     */
    public function addFullName($name)
    {
        $this->constructElement('FN', $name);
    }

    /**
     * Construct the element
     *
     * @param string       $element   Name of the vCard element
     * @param string|array $value     Value to construct. If it's an array, make it a list using
     *                                the proper `delimiter`
     * @param string       $delimiter Delimiter to use for lists given via `$value` array.
     *                                Default: `comma`. Any other value is interpreted as semicolon.
     *
     * @return null
     */
    private function constructElement($element, $value, $delimiter = 'comma')
    {
        $value = is_array($value) ? array_map(array($this, 'cleanString'), $value,
            array($delimiter)) : $this->cleanString($value);
        $this->setProperty($element, vsprintf(\contacts\config::get($element), $value));
    }

    /**
     * Clean a string be escaping `,` and `;` and `:`
     *
     * @param string|array $string    String to escape
     * @param string       $delimiter Delimiter to create a list from an array. Default: `comma`.
     *                                Any other value is interpreted as semicolon.
     *
     * @return string|null Returns cleaned string or `null`
     */
    private function cleanString($string, $delimiter = 'comma')
    {
        // If it's an array, clean individual strings and return a comma-delimited list of array values
        if (is_array($string)) {
            foreach ($string as $key => $value) {
                $string[$key] = $this->cleanString($value);
            }

            return $delimiter == 'comma' ? implode(',', $string) : implode(';', $string);
        }
        $search = array(',', ';', ':');
        $replace = array('\,', '\;', '\:');

        return empty($string) ? null : str_replace($search, $replace, $string);
    }

    /**
     * Set vCard property
     *
     * @param string $element vCard element to set
     * @param string $value   Value to set vCard element to
     *
     * @return null
     */
    private function setProperty($element, $value)
    {
        if (!in_array($element, $this->multiplePropertiesAllowed) && isset($this->definedElements[$element])) {
            throw new \Exception('You can only set "'.$element.'" once.');
        }
        // Define that we set this element
        $this->definedElements[$element] = true;
        // Add property
        $this->properties[] = array(
            'key' => $element,
            'value' => $value,
        );
    }

    /**
     * Add name to vCard
     *
     * RFC 2426 p. 8
     *
     * This type is based on the semantics of the X.520 individual name
     * attributes. The property MUST be present in the vCard object.
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.1.2 RFC 2426 Section 3.1.2 (p. 8)
     *
     * @param string $lastName       Family name
     * @param string $firstName      Given name. Default: null
     * @param string $additionalName Middle name(s). Comma-delimited list. Default: null
     * @param string $prefix         Honorific prefix(es). Comma-delimited list. Default: null
     * @param string $suffix         Honorific suffix(es). Comma-delimited list. Default: null
     *
     * @return null
     */
    public function addName($lastName, $firstName = null, $additionalName = null, $prefix = null, $suffix = null)
    {
        $this->constructElement('N', array($lastName, $firstName, $additionalName, $prefix, $suffix));
    }

    /**
     * Add nickname(s) to vCard
     *
     * RFC 2426 pp. 8–9
     *
     * The nickname is the descriptive name given instead
     * of or in addition to the one belonging to a person, place, or thing.
     * It can also be used to specify a familiar form of a proper name
     * specified by the `FN` or `N` types.
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.1.3 RFC 2426 Section 3.1.3 (pp. 8-9)
     *
     * @param string|array $name Nickname(s). Comma-delimited list of nicknames (or array)
     *
     * @return null
     */
    public function addNickname($name)
    {
        $name = is_array($name) ? $name : explode(',', $name);
        $this->constructElement('NICKNAME', array($name));
    }

    /**
     * Add photo. Not currently supported.
     *
     * RFC 2426 pp. 9-10
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.1.4 RFC 2426 Section 3.1.4 (pp. 9-10)
     *
     * @param string $photo URL-referenced or base-64 encoded photo
     * @param bool   $isUrl Optional. Is it a URL-referenced photo or a base-64 encoded photo. Default: `true`
     */
    public function addPhoto($photo, $isUrl = true)
    {
        if ($isUrl) {
            // Set directly rather than going through $this->constructElement to avoid escaping valid URL characters
            if (!empty($this->sanitizeUrl($photo))) {
                $this->setProperty('PHOTO', vsprintf(\contacts\config::get('PHOTO-BINARY'),
                    array('JPEG', base64_encode($this->getData($photo)))));
            }
        } else {
            $this->setProperty('PHOTO', vsprintf(\contacts\config::get('PHOTO-BINARY'), array('JPEG', $photo)));
        }
    }

    /**
     * Add birthday to vCard
     *
     * RFC 2426 p. 10
     *
     * Standard allows for date-time values. Not supported in this class.
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.1.5 RFC 2426 Section 3.1.5 (p. 10)
     *
     * @param int $year  Year of birth. If no year given, use iOS custom date field to indicate birth month and day
     *                   only. Default: null
     * @param int $month Month of birth.
     * @param int $day   Day of birth.
     *
     * @return null
     */
    public function addBirthday($year = null, $month, $day)
    {
        if ($year) {
            $this->constructElement('BDAY', array($year, $month, $day));
        } else {
            $this->definedElements['BDAY'] = true; // Define `BDAY` element
            $this->constructElement('BDAY-NO-YEAR', array($month, $day));
        }
    }

    /**
     * Add address to vCard
     *
     * RFC 2426 pp. 10–11
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.2.1 RFC 2426 Section 3.2.1 (pp. 10-11)
     *
     * @param string       $poBox    Post office box number
     * @param string       $extended Extended address
     * @param string       $street   Street address
     * @param string       $city     City
     * @param string       $state    State/province
     * @param int          $zip      Postal code (5 digits per United States standard)
     * @param string       $country  Country
     * @param string|array $type     Comma-delimited list of address types
     *                               * Valid `$type`s:
     *                               * `dom` - domestic delivery address
     *                               * `intl` - international delivery address
     *                               * `postal` - postal delivery address
     *                               * `parcel` - parcel delivery address
     *                               * `home` - residence delivery address
     *                               * `work` - work delivery address
     *                               * `pref` - preferred delivery address when more than one address is specified
     *                               * Default: `intl,postal,parcel,work`
     *
     * @return null
     */
    public function addAddress(
        $poBox = null,
        $extended = null,
        $street = null,
        $city = null,
        $state = null,
        $zip = null,
        $country = null,
        $type = null
    ) {
        $type = is_array($type) ? $type : explode(',', $type);
        // Make sure all `$type`s are valid. If invalid `$type`(s), revert to standard default.
        $type = $this->inArrayAll($type, $this->validAddressTypes) ? $type : 'intl,postal,parcel,work';
        $this->constructElement('ADR', array($type, $poBox, $extended, $street, $city, $state, $zip, $country));
    }

    /**
     * Add mailing label to vCard
     *
     * RFC 2426 p. 12
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.2.2 RFC 2426 Section 3.2.2 (p. 12)
     *
     * @param string       $label Mailing label
     * @param string|array $type  Comma-delimited list of mailing label types (or array)
     *                            * Valid `$type`s:
     *                            * `dom` - domestic delivery address
     *                            * `intl` - international delivery address
     *                            * `postal` - postal delivery address
     *                            * `parcel` - parcel delivery address
     *                            * `home` - residence delivery address
     *                            * `work` - work delivery address
     *                            * `pref` - preferred delivery address when more than one address is specified
     *                            * Default: `intl,postal,parcel,work`
     *
     * @return null
     */
    public function addLabel($label, $type = null)
    {
        $type = is_array($type) ? $type : explode(',', $type);
        // Make sure all `$type`s are valid. If invalid `$type`(s), revert to standard default.
        $type = $this->inArrayAll($type, $this->validAddressTypes) ? $type : 'intl,postal,parcel,work';
        $this->constructElement('LABEL', array($type, $label));
    }

    /**
     * Add telephone number to vCard
     *
     * RFC 2426 p. 13
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.3.1 RFC 2426 Section 3.3.1 (p. 13)
     *
     * @param int          $phone Phone number (numbers only)
     * @param string|array $type  Comma-delimited list of telephone types (or array)
     *                            * Valid `$type`s:
     *                            * `home` - telephone number associated with a residence
     *                            * `msg` - telephone number has voice messaging support
     *                            * `work` - telephone number associated with a place of work
     *                            * `pref` - preferred-use telephone number
     *                            * `voice` - voice telephone number
     *                            * `fax` - facsimile telephone number
     *                            * `cell` - cellular telephone number
     *                            * `video` - video conferencing telephone number
     *                            * `pager` - paging device telephone number
     *                            * `bbs` - bulletin board system telephone number
     *                            * `modem` - MODEM connected telephone number
     *                            * `car` - car-phone telephone number
     *                            * `isdn` - ISDN service telephone number
     *                            * `pcs` - personal communication services telephone number
     *                            * `iphone` - Non-standard type to indicate phone is an iPhone
     *                            * Default: `voice`
     *
     * @return null
     */
    public function addTelephone($phone, $type = null)
    {
        $type = is_array($type) ? $type : explode(',', $type);
        // Make sure all `$type`s are valid. If invalid `$type`(s), revert to standard default.
        $type = $this->inArrayAll($type, $this->validTelephoneTypes) ? $type : 'voice';
        $this->constructElement('TEL', array($type, $this->sanitizePhone($phone)));
    }

    /**
     * Add email address to vCard
     *
     * RFC 2426 p. 14
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.3.2 RFC 2426 Section 3.3.2 (p. 14)
     *
     * @param string       $email Email address
     * @param string|array $type  Comma-delimited list of email address types (or array)
     *                            * Valid `$type`s:
     *                            * `internet` - Internet addressing type
     *                            * `x400` - X.400 addressing type
     *                            * `pref` - preferred-use email address when more than one is specified
     *                            * Another IANA registered address type can also be specified
     *                            * A non-standard value can also be specified
     *                            * Default: `internet`
     *
     * @return null
     */
    public function addEmail($email, $type = null)
    {
        $type = empty($type) ? 'internet' : $type;
        $type = is_array($type) ? $type : explode(',', $type);
        $this->constructElement('EMAIL', array($type, $this->sanitizeEmail($email)));
    }

    /**
     * Add email software to vCard
     *
     * RFC 2426 pp. 14-15
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.3.3 RFC 2426 Section 3.3.3 (pp. 14-15)
     *
     * @param string $mailer Software used by recipient to send/receive email
     *
     * @return null
     */
    public function addMailer($mailer)
    {
        $this->constructElement('MAILER', $mailer);
    }

    /**
     * Add time zone to vCard
     *
     * RFC 2426 p. 15
     *
     * Standard allows for UTC to be represented as a single text value. Not supported in this class.
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.4.1 RFC 2426 Section 3.4.1 (p. 15)
     * @link http://www.iana.org/time-zones Internet Assigned Numbers Authority (IANA) Time Zone Database
     *
     * @param string $timeZone Time zone (UTC-offset) as a number between -14 and +12 (inclusive - do not zero-pad).
     *                         Examples: `-7`, `-12`, `-12:00`, `10:30`
     *
     * @return null
     */
    public function addTimeZone($timeZone)
    {
        $this->constructElement('TZ', $this->sanitizeTimeZone($timeZone));
    }

    /**
     * Add latitude and longitude to vCard
     *
     * RFC 2426 pp. 15-16
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.4.2 RFC 2426 Section 3.4.2 (pp. 15-16)
     *
     * @param string $lat  Geographic Positioning System latitude (decimal) (must be a number between -90 and 90)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     * @param string $long Geopgraphic Positioning System longitude (decimal) (must be a number between -180 and 180)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     *
     * @return null
     */
    public function addLatLong($lat, $long)
    {
        $this->constructElement('GEO', $this->sanitizeLatLong($lat, $long));
    }

    /**
     * Add job title to vCard
     *
     * RFC 2426 pp. 16-17
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.5.1 RFC 2426 Section 3.5.1 (pp. 16-17)
     *
     * @param string $title Job title
     *
     * @return null
     */
    public function addTitle($title)
    {
        $this->constructElement('TITLE', $title);
    }

    /**
     * Add role, occupation, or business category to vCard
     *
     * RFC 2426 p. 17
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.5.2 RFC 2426 Section 3.5.2 (p. 17)
     *
     * @param string $role Job role
     *
     * @return null
     */
    public function addRole($role)
    {
        $this->constructElement('ROLE', $role);
    }

    /**
     * Add logo. Not currently supported.
     *
     * RFC 2426 pp. 17-18
     *
     * Standard allows for binary photo data. Not supported in this class (URL-referenced photos only)
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.5.3 RFC 2426 Section 3.5.3 (pp. 17-18)
     *
     * @param null $logo Not supported
     */
    public function addLogo($logo)
    {
        // Set directly rather than going through $this->constructElement to avoid escaping valid URL characters
        if (!empty($this->sanitizeUrl($logo))) {
            $mimetype = str_replace('image/', '', getimagesize($logo)['mime']);
            $this->setProperty('PHOTO', vsprintf(\contacts\config::get('PHOTO-BINARY'),
                array($mimetype, base64_encode(file_get_contents($logo)))));
        }
    }

    /**
     * Add agent. Not currently supported.
     *
     * RFC 2426 pp. 18-19
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.5.4 RFC 2426 Section 3.5.4 (pp. 18-19)
     *
     * @param null $agent Not supported
     */
    public function addAgent($agent)
    {
    }

    /**
     * Add organization name to vCard.
     *
     * RFC 2426 p. 19
     *
     * Structured type consisting of the organization name, followed by
     * one or more levels of organizational unit names (semi-colon delimited).
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.5.5 RFC 2426 Section 3.5.5 (p. 19)
     *
     * @param string|array $organization Semi-colon delimited list of organization units (or array)
     *
     * @return null
     */
    public function addOrganization($organization)
    {
        $organization = is_array($organization) ? $organization : explode(';', $organization);
        $this->constructElement('ORG', array($organization), 'semicolon');
    }

    /**
     * Add categories to vCard
     *
     * RFC 2426 pp. 19-20
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.6.1 RFC 2426 Section 3.6.1 (pp. 19-20)
     *
     * @param string|array $categories Comma-delimited list of categories (or array)
     *
     * @return null
     */
    public function addCategories($categories)
    {
        $categories = is_array($categories) ? $categories : explode(',', $categories);
        $this->constructElement('CATEGORIES', array($categories));
    }

    /**
     * Add note, supplemental information, or a comment to vCard
     *
     * RFC 2426 p. 20
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.6.2 RFC 2426 Section 3.6.2 (p. 20)
     *
     * @param string $note Note
     *
     * @return null
     */
    public function addNote($note)
    {
        $this->constructElement('NOTE', $note);
    }

    /**
     * Add identifier for the product that created the vCard
     *
     * RFC 2426 pp. 20-21
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.6.3 RFC 2426 Section 3.6.3 (pp. 20-21)
     *
     * @param string $productId Product ID
     *
     * @return null
     */
    public function addProductId($productId)
    {
        $this->constructElement('PRODID', $productId);
    }

    /**
     * Add sort string to specify the family name or given name text to be used for national-language-specific sorting
     * of the FN and N types
     *
     * RFC 2426 pp. 21-22
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.6.5 RFC 2426 Section 3.6.5 (pp. 21-22)
     *
     * @param string $sortString Sort string to use for `FN` and `N`
     *
     * @return null
     */
    public function addSortString($sortString)
    {
        $this->constructElement('SORT-STRING', $sortString);
    }

    /**
     * Add sound. Not currently supported.
     *
     * RFC 2426 pp. 22-23
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.6.6 RFC 2426 Section 3.6.6 (pp. 22-23)
     *
     * @param null $sound Not supported
     */
    public function addSound($sound)
    {
    }

    /**
     * Add a globally unique identifier corresponding to the individual to the vCard
     *
     * RFC 2426 p. 23
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.6.7 RFC 2426 Section 3.6.7 (p. 23)
     *
     * @param string $uniqueIdentifier Unique identifier
     *
     * @return null
     */
    public function addUniqueIdentifier($uniqueIdentifier)
    {
        $this->constructElement('UID', $uniqueIdentifier);
    }

    /**
     * Add uniform resource locator (URL) to vCard
     *
     * RFC 2426 p. 24
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.6.8 RFC 2426 Section 3.6.8 (p. 24)
     *
     * @param string $url URL
     *
     * @return null
     */
    public function addUrl($url)
    {
        // Set directly rather than going through $this->constructElement to avoid escaping valid URL characters
        $this->setProperty('URL', vsprintf(\contacts\config::get('URL'), $this->sanitizeUrl($url)));
    }

    /**
     * Add access classification to vCard
     *
     * RFC 2426 p. 25
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.7.1 RFC 2426 Section 3.7.1 (p. 25)
     *
     * @param string $classification Access classification. Default: `PUBLIC`
     *                               * Valid classifications:
     *                               * PUBLIC
     *                               * PRIVATE
     *                               * CONFIDENTIAL
     *
     * @return null
     */
    public function addClassification($classification = null)
    {
        $classification = $this->inArrayAll([$classification],
            $this->validClassifications) ? $classification : 'PUBLIC';
        $this->constructElement('CLASS', $classification);
    }

    /**
     * Add key. Not currently supported.
     *
     * RFC 2426 pp. 25-26
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.7.2 RFC 2426 Section 3.7.2 (pp. 25-26)
     *
     * @param null $key Not supported
     */
    public function addKey($key)
    {
    }

    /**
     * Add custom extended type to vCard
     *
     * RFC 2426 p. 26
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.8 RFC 2426 Section 3.8 (p. 26)
     *
     * @param string $label Label for custom extended type
     * @param string $value Value of custom extended type
     *
     * @return null
     */
    public function addExtendedType($label, $value)
    {
        $this->constructElement('X-', array($label, $value));
    }

    /**
     * Add custom iOS anniversary to vCard
     *
     * @param string $anniversary Anniversary date
     *
     * @return null
     */
    public function addAnniversary($anniversary)
    {
        $anniversary = date('Y-m-d', strtotime($anniversary));
        $this->constructElement('ANNIVERSARY', array($anniversary, $this->extendedItemCount));
        $this->extendedItemCount++;
    }

    /**
     * Add custom iOS supervisor to vCard
     *
     * @param string $supervisor Supervisor name
     *
     * @return null
     */
    public function addSupervisor($supervisor)
    {
        $this->constructElement('SUPERVISOR', array($supervisor, $this->extendedItemCount));
        $this->extendedItemCount++;
    }

    /**
     * Add custom iOS spouse to vCard
     *
     * @param string $spouse Spouse name
     *
     * @return null
     */
    public function addSpouse($spouse)
    {
        $this->constructElement('SPOUSE', array($spouse, $this->extendedItemCount));
        $this->extendedItemCount++;
    }

    /**
     * Add custom iOS child to vCard
     *
     * @param string $child Child name
     *
     * @return null
     */
    public function addChild($child)
    {
        $this->constructElement('CHILD', array($child, $this->extendedItemCount));
        $this->extendedItemCount++;
    }

    /**
     * Build the vCard
     *
     * @param bool   $write    Write vCard to file or not. Default: false
     * @param string $filename Name of vCard file. Default: timestamp
     *
     * @return string vCard as a string
     */
    public function buildVcard($write = false, $filename = null)
    {
        $filename = empty($filename) ? date('Y.m.d.H.i.s') : $filename;
        $this->addRevision();
        $string = "BEGIN:VCARD\r\n";
        $string .= "VERSION:3.0\r\n";
        foreach ($this->properties as $property) {
            $value = str_replace('\r\n', "\r\n", $property['value']);
            $string .= $this->fold($value."\r\n");
        }
        $string .= "END:VCARD\r\n\r\n";
        if ($write) {
            $this->writeFile($filename.'.vcf', $string, true);
        }

        return $string;
    }

    /**
     * Add revision date to vCard (For example, `1995-10-31T22:27:10Z`)
     *
     * RFC 2426 p. 21
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.6.4 RFC 2426 Section 3.6.4 (p. 21)
     *
     * @param null
     *
     * @return null
     */
    public function addRevision()
    {
        // Set directly rather than going through $this->constructElement to avoid escaping valid timestamp characters
        $this->setProperty('REV', vsprintf(\contacts\config::get('REV'), date('Y-m-d\TH:i:s\Z')));
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
    protected function fold($text)
    {
        return (strlen($text) <= 75) ? $text : substr(chunk_split($text, 73, "\r\n "), 0, -3);
    }
}

?>
