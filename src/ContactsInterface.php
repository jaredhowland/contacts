<?php
/**
 * Interface to create contacts
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @version 2020-01-24
 * @since   2016-10-05
 */

namespace Contacts;

/**
 * ContactsInterface for all `Contacts` child classes
 */
interface ContactsInterface
{
    public function addFullName(string $name);

    public function addName(
        string $lastName,
        string $firstName,
        string $additionalNames,
        string $prefixes,
        string $suffixes
    );

    public function addNicknames(array $names);

    public function addPhoto(string $photo, bool $isUrl);

    public function addBirthday(int $year, int $month, int $day);

    public function addAddress(
        string $poBox,
        string $extended,
        string $street,
        string $city,
        string $state,
        string $zip,
        string $country,
        array $types
    );

    public function addLabel(string $label, array $types);

    public function addTelephone(string $phone, array $types);

    public function addEmail(string $email, array $types);

    public function addMailer(string $mailer);

    public function addTimeZone(string $timeZone);

    public function addLatLong(float $lat, float $long);

    public function addTitle(string $title);

    public function addRole(string $role);

    public function addLogo(string $logo);

    public function addAgent(string $agent);

    public function addOrganizations(array $organizations);

    public function addCategories(array $categories);

    public function addNote(string $note);

    public function addProductId(string $productId);

    public function addRevision(string $dateTime);

    public function addSortString(string $sortString);

    public function addSound(string $sound);

    public function addUniqueIdentifier(string $uniqueIdentifier);

    public function addUrl(string $url);

    public function addClassification(string $classification);

    public function addKey(string $key);

    public function addExtendedType(string $label, string $value);
}
