<?php
/**
 * Create a vCard
 *
 * Known issues:
 *   * Date-time values not supported for `BDAY` field (only date values). No plans to implement.
 *   * Text values not supported for `TZ` field (only UTC-offset values). No plans to implement.
 *   * The following vCard elements are not currently supported (no plans to implement):
 *     * AGENT
 *     * SOUND
 *     * KEY
 *
 * Inspired by https://github.com/jeroendesloovere/vcard
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @version 2023-09-06
 * @since   2016-10-05
 *
 */

namespace Contacts;

use GuzzleHttp\Exception\GuzzleException;

/**
 * vCard class to create a vCard. Extends `Contacts` and implements `ContactsInterface`
 */
class Vcard implements ContactsInterface
{
    use Helpers\Generic;
    use Helpers\Vcard;

    /**
     * @var Options $options Object containing all options for this class
     */
    private Options $options;

    /**
     * @var Properties $properties Object containing all the set properties
     */
    private Properties $properties;

    /**
     * Construct Vcard Class
     *
     * @param Options|null $options
     */
    public function __construct(Options $options = null)
    {
        $this->options = $options ?? new Options();
        $this->properties = new Properties();
    }

    /**
     * Get defined properties array
     *
     * @return array Array of defined properties
     */
    public function getProperties(): array
    {
        return $this->properties->get();
    }

    /**
     * Get defined elements in `Properties` object
     *
     * @return array
     */
    public function getDefinedElements(): array
    {
        return $this->properties->getDefinedElements();
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
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addFullName(string $name): Vcard
    {
        $this->properties->constructElement('FN', $name);

        return $this;
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
     * @param string $lastName Family name
     * @param string|null $firstName Given name. Default: `null`
     * @param string|null $additionalNames Middle name(s). Comma-delimited. Default: `null`
     * @param string|null $prefixes Honorific prefix(es). Comma-delimited. Default: `null`
     * @param string|null $suffixes Honorific suffix(es). Comma-delimited. Default: `null`
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addName(
        string $lastName,
        string $firstName = null,
        string $additionalNames = null,
        string $prefixes = null,
        string $suffixes = null
    ): Vcard {
        $additionalNames = $this->removeSpacesFromList($additionalNames);
        $prefixes = $this->removeSpacesFromList($prefixes);
        $suffixes = $this->removeSpacesFromList($suffixes);
        // Set directly rather than going through $this->properties->constructElement to avoid escaping valid commas in `$additionalNames`, `$prefixes`, and `$suffixes`
        $this->properties->setProperty(
            'N',
            vsprintf(Config::get('N'), [$lastName, $firstName, $additionalNames, $prefixes, $suffixes])
        );

        return $this;
    }

    /**
     * Add nickname to vCard
     * Make list comma delimited if you have more than one nickname to add
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
     * @param string $name Nickname(s), comma delimited string if more than one nickname
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addNickname(string $name): Vcard
    {
        $name = $this->removeSpacesFromList($name);
        // Set directly rather than going through $this->properties->constructElement to avoid escaping valid commas in `$additionalNames`, `$prefixes`, and `$suffixes`
        $this->properties->setProperty(
            'NICKNAME',
            vsprintf(Config::get('NICKNAME'), [$name])
        );

        return $this;
    }

    /**
     * Add photo
     *
     * RFC 2426 pp. 9-10
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.1.4 RFC 2426 Section 3.1.4 (pp. 9-10)
     *
     * @param string $photo URL-referenced or base-64 encoded photo
     * @param bool $isUrl Optional. Is it a URL-referenced photo or a base-64 encoded photo? Default: `true`
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     * @throws GuzzleException
     */
    public function addPhoto(string $photo, bool $isUrl = true): Vcard
    {
        $this->properties->photoProperty('PHOTO', $photo, $isUrl);

        return $this;
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
     * @param int $month Month of birth.
     * @param int $day Day of birth.
     * @param int|null $year Year of birth. If no year given, use iOS custom date field to indicate birth month and day
     *                    only. Default: `null`
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addBirthday(int $month, int $day, ?int $year = null): Vcard
    {
        if (empty($year)) {
            $this->properties->setDefinedElements('BDAY'); // Define `BDAY` element
            $this->properties->constructElement('BDAY-NO-YEAR', [$month, $day]);

            return $this;
        }

        $this->properties->setDefinedElements('BDAY-NO-YEAR'); // Define `BDAY-NO-YEAR` element
        $this->properties->constructElement('BDAY', [$year, $month, $day]);

        return $this;
    }

    /**
     * Add address to vCard
     *
     * RFC 2426 pp. 10–11
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.2.1 RFC 2426 Section 3.2.1 (pp. 10-11)
     *
     * @param string|null $poBox Post office box number
     * @param string|null $extended Extended address
     * @param string|null $street Street address
     * @param string|null $city City
     * @param string|null $state State/province
     * @param string|null $zip Postal code
     * @param string|null $country Country
     * @param array $types Array of address types
     *                         * Valid `$types`s:
     *                         * `dom` - domestic delivery address
     *                         * `intl` - international delivery address
     *                         * `postal` - postal delivery address
     *                         * `parcel` - parcel delivery address
     *                         * `home` - residence delivery address
     *                         * `work` - work delivery address
     *                         * `pref` - preferred delivery address when more than one address is specified
     *                         * Default: `intl,postal,parcel,work`
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addAddress(
        string $poBox = null,
        string $extended = null,
        string $street = null,
        string $city = null,
        string $state = null,
        string $zip = null,
        string $country = null,
        array $types = ['intl', 'postal', 'parcel', 'work']
    ): Vcard {
        // Make sure all `$types`s are valid. If invalid `$types`(s), revert to standard default.
        if ($this->inArrayAll($types, $this->properties->getValidAddressTypes())) {
            $this->properties->constructElement(
                'ADR',
                [$types, $poBox, $extended, $street, $city, $state, $zip, $country]
            );

            return $this;
        }

        $typesMessage = implode(', ', $types);
        throw new ContactsException("Invalid address type(s): '$typesMessage'");
    }

    /**
     * Add mailing label to vCard
     *
     * RFC 2426 p. 12
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.2.2 RFC 2426 Section 3.2.2 (p. 12)
     *
     * @param string $label Mailing label
     * @param array $types Array of mailing label types
     *                      * Valid `$types`s:
     *                      * `dom` - domestic delivery address
     *                      * `intl` - international delivery address
     *                      * `postal` - postal delivery address
     *                      * `parcel` - parcel delivery address
     *                      * `home` - residence delivery address
     *                      * `work` - work delivery address
     *                      * `pref` - preferred delivery address when more than one address is specified
     *                      * Default: `intl,postal,parcel,work`
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addLabel(string $label, array $types = []): Vcard
    {
        // Make sure all `$types`s are valid. If invalid `$types`(s), revert to standard default.
        $types = $this->inArrayAll($types, $this->properties->getValidAddressTypes()) ? $types : [
            'intl',
            'postal',
            'parcel',
            'work'
        ];
        $this->properties->constructElement('LABEL', [$types, $label]);

        return $this;
    }

    /**
     * Add telephone number to vCard
     *
     * RFC 2426 p. 13
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.3.1 RFC 2426 Section 3.3.1 (p. 13)
     *
     * @param string|null $phone Phone number
     * @param array $types Array of telephone types
     *                      * Valid `$types`s:
     *                      * `home` - telephone number associated with a residence
     *                      * `msg` - telephone number has voice messaging support
     *                      * `work` - telephone number associated with a place of work
     *                      * `pref` - preferred-use telephone number
     *                      * `voice` - voice telephone number
     *                      * `fax` - facsimile telephone number
     *                      * `cell` - cellular telephone number
     *                      * `video` - video conferencing telephone number
     *                      * `pager` - paging device telephone number
     *                      * `bbs` - bulletin board system telephone number
     *                      * `modem` - MODEM connected telephone number
     *                      * `car` - car-phone telephone number
     *                      * `isdn` - ISDN service telephone number
     *                      * `pcs` - personal communication services telephone number
     *                      * `iphone` - Non-standard type to indicate phone is an iPhone
     *                      * Default: `voice`
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addTelephone(string $phone = null, array $types = []): Vcard
    {
        // Format phone number if requested
        if ($this->options->isFormatUsTelephone() && !is_null($phone)) {
            $phone = $this->formatUsTelephone($phone);
        }
        // Make sure all `$types`s are valid. If invalid `$types`(s), revert to standard default.
        $types = $this->inArrayAll($types, $this->properties->getValidTelephoneTypes()) ? $types : ['voice'];
        $this->properties->constructElement('TEL', [$types, $phone]);

        return $this;
    }

    /**
     * Add email address to vCard
     *
     * RFC 2426 p. 14
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.3.2 RFC 2426 Section 3.3.2 (p. 14)
     *
     * @param string|null $email Email address
     * @param array|null $types Array of email address types
     *                      * Valid `$types`s:
     *                      * `internet` - Internet addressing type
     *                      * `x400` - X.400 addressing type
     *                      * `pref` - preferred-use email address when more than one is specified
     *                      * Another IANA registered address type can also be specified
     *                      * A non-standard value can also be specified
     *                      * Default: `internet`
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addEmail(string $email = null, array $types = null): Vcard
    {
        $types = empty($types) ? ['internet'] : $types;
        $email = $this->sanitizeEmail($email);
        $this->properties->constructElement('EMAIL', [$types, $email]);

        return $this;
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
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addMailer(string $mailer): Vcard
    {
        $this->properties->constructElement('MAILER', $mailer);

        return $this;
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
     * @param string $timeZone Time zone (UTC-offset) as a number between -14 and +12 (inclusive).
     *                         Examples: `-7`, `-12`, `-12:00`, `10:30`
     *                         Invalid time zone values return `+00:00`
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addTimeZone(string $timeZone): Vcard
    {
        if ($this->sanitizeTimeZone($timeZone)) {
            $this->properties->constructElement('TZ', $this->sanitizeTimeZone($timeZone));
        }

        return $this;
    }

    /**
     * Add latitude and longitude to vCard
     *
     * RFC 2426 pp. 15-16
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.4.2 RFC 2426 Section 3.4.2 (pp. 15-16)
     *
     * @param float $lat Geographic Positioning System latitude (decimal) (must be a number between -90 and 90)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     * @param float $long Geographic Positioning System longitude (decimal) (must be a number between -180 and 180)
     *
     * **FORMULA**: decimal = degrees + minutes/60 + seconds/3600
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addLatLong(float $lat, float $long): Vcard
    {
        if ($this->sanitizeLatLong($lat, $long)) {
            $this->properties->constructElement('GEO', $this->sanitizeLatLong($lat, $long));
        }

        return $this;
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
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addTitle(string $title): Vcard
    {
        $this->properties->constructElement('TITLE', $title);

        return $this;
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
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addRole(string $role): Vcard
    {
        $this->properties->constructElement('ROLE', $role);

        return $this;
    }

    /**
     * Add logo
     *
     * RFC 2426 pp. 17-18
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.5.3 RFC 2426 Section 3.5.3 (pp. 17-18)
     *
     * @param string $logo URL-referenced or base-64 encoded photo
     * @param bool $isUrl Optional. Is it a URL-referenced photo or a base-64 encoded photo? Default: `true`
     *
     * @return $this
     *
     * @throws ContactsException|GuzzleException if an element that can only be defined once is defined more than once
     */
    public function addLogo(string $logo, bool $isUrl = true): Vcard
    {
        $this->properties->photoProperty('LOGO', $logo, $isUrl);

        return $this;
    }

    /**
     * Add agent. Not currently supported.
     *
     * RFC 2426 pp. 18-19
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.5.4 RFC 2426 Section 3.5.4 (pp. 18-19)
     *
     * @param string|null $agent Not supported. Default `null`
     *
     * @throws ContactsException if this unsupported method is called
     */
    public function addAgent(string $agent = null): void
    {
        throw new ContactsException('"AGENT" is not a currently supported element.');
    }

    /**
     * Add organization name to vCard.
     *
     * RFC 2426 p. 19
     *
     * Structured type consisting of the organization name, followed by
     * one or more levels of organizational unit names (semicolon delimited).
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.5.5 RFC 2426 Section 3.5.5 (p. 19)
     *
     * @param array $organizations Array of organization units
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addOrganizations(array $organizations): Vcard
    {
        $this->properties->constructElement('ORG', [$organizations], ';');

        return $this;
    }

    /**
     * Add categories to vCard
     *
     * RFC 2426 pp. 19-20
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.6.1 RFC 2426 Section 3.6.1 (pp. 19-20)
     *
     * @param array $categories Array of categories
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addCategories(array $categories): Vcard
    {
        $this->properties->constructElement('CATEGORIES', [$categories]);

        return $this;
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
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addNote(string $note): Vcard
    {
        $this->properties->constructElement('NOTE', $note);

        return $this;
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
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addProductId(string $productId): Vcard
    {
        $this->properties->constructElement('PRODID', $productId);

        return $this;
    }

    /**
     * Add revision date to vCard (For example, `1995-10-31T22:27:10Z`)
     *
     * RFC 2426 p. 21
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.6.4 RFC 2426 Section 3.6.4 (p. 21)
     *
     * @param string|null $dateTime Date and time to add to card as the revision time. Default: `creation timestamp`
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addRevision(string $dateTime = null): Vcard
    {
        $dateTime = $this->getDateTime($dateTime);
        // Set directly rather than going through $this->properties->constructElement to avoid escaping valid timestamp characters
        $this->properties->setProperty('REV', vsprintf(Config::get('REV'), [$dateTime]));

        return $this;
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
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addSortString(string $sortString): Vcard
    {
        $this->properties->constructElement('SORT-STRING', $sortString);

        return $this;
    }

    /**
     * Add sound. Not currently supported.
     *
     * RFC 2426 pp. 22-23
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.6.6 RFC 2426 Section 3.6.6 (pp. 22-23)
     *
     * @param string|null $sound Not supported. Default `null`
     *
     * @throws ContactsException if this unsupported method is called
     */
    public function addSound(string $sound = null): void
    {
        throw new ContactsException('"SOUND" is not a currently supported element.');
    }

    /**
     * Add a globally unique identifier corresponding to the individual to the vCard
     *
     * RFC 2426 p. 23
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.6.7 RFC 2426 Section 3.6.7 (p. 23)
     *
     * @param string|null $uniqueIdentifier Unique identifier. Default: `PHP-generated unique identifier`
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addUniqueIdentifier(string $uniqueIdentifier = null): Vcard
    {
        $uniqueIdentifier = $uniqueIdentifier ?? uniqid('', true);
        $this->properties->constructElement('UID', $uniqueIdentifier);

        return $this;
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
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addUrl(string $url): Vcard
    {
        if (!empty($this->sanitizeUrl($url))) {
            // Set directly rather than going through $this->properties->constructElement to avoid escaping valid URL characters
            $this->properties->setProperty('URL', vsprintf(Config::get('URL'), [$this->sanitizeUrl($url)]));
        }

        return $this;
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
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addClassification(string $classification = 'PUBLIC'): Vcard
    {
        if ($this->inArrayAll([$classification], $this->properties->getValidClassifications())) {
            $this->properties->constructElement('CLASS', $classification);

            return $this;
        }

        throw new ContactsException("Invalid classification: '$classification'");
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
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addExtendedType(string $label, string $value): Vcard
    {
        $this->properties->constructElement('X-', [$label, $value]);

        return $this;
    }

    /**
     * Add key. Not currently supported.
     *
     * RFC 2426 pp. 25-26
     *
     * @link https://tools.ietf.org/html/rfc2426#section-3.7.2 RFC 2426 Section 3.7.2 (pp. 25-26)
     *
     * @param string|null $key Not supported. Default `null`
     *
     * @throws ContactsException if this unsupported method is called
     */
    public function addKey(string $key = null): void
    {
        throw new ContactsException('"KEY" is not a currently supported element.');
    }

    /**
     * Add custom iOS anniversary to vCard
     *
     * @param string $anniversary Anniversary date
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addAnniversary(string $anniversary): Vcard
    {
        if (is_int(strtotime($anniversary))) {
            $anniversary = date('Y-m-d', strtotime($anniversary));
            $this->properties->constructElement(
                'ANNIVERSARY',
                [$anniversary, $this->properties->getExtendedItemCount()]
            );

            return $this;
        }

        throw new ContactsException("Invalid date for anniversary: '$anniversary'");
    }

    /**
     * Add custom iOS supervisor to vCard
     *
     * @param string $supervisor Supervisor name
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addSupervisor(string $supervisor): Vcard
    {
        $this->properties->constructElement('SUPERVISOR', [$supervisor, $this->properties->getExtendedItemCount()]);

        return $this;
    }

    /**
     * Add custom iOS spouse to vCard
     *
     * @param string $spouse Spouse name
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addSpouse(string $spouse): Vcard
    {
        $this->properties->constructElement('SPOUSE', [$spouse, $this->properties->getExtendedItemCount()]);

        return $this;
    }

    /**
     * Add custom iOS child to vCard
     *
     * @param string $child Child name
     *
     * @return $this
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function addChild(string $child): Vcard
    {
        $this->properties->constructElement('CHILD', [$child, $this->properties->getExtendedItemCount()]);

        return $this;
    }

    /**
     * Build the vCard
     *
     * @param bool $write Write vCard to file or not. Default: `false`
     * @param string|null $filename Name of vCard file. Default: `timestamp`
     *
     * @return string vCard as a string
     *
     * @throws ContactsException if an element that can only be defined once is defined more than once
     */
    public function buildVcard(bool $write = false, string $filename = null): string
    {
        $filename = $this->getFileName($filename);
        if (!isset($this->properties->getDefinedElements()['REV'])) {
            $this->addRevision();
        }
        $string = "BEGIN:VCARD\r\n";
        $string .= "VERSION:3.0\r\n";
        $string .= $this->properties->addProperties($this->properties->get());
        $string .= "END:VCARD\r\n\r\n";
        if ($write) {
            $this->writeFile($filename . '.vcf', $string, true);
        }

        return $string;
    }
}
