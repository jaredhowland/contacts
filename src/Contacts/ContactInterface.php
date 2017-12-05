<?php
/**
 * Create a vCard
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @version 2017-12-05
 * @since   2016-10-05
 *
 */

namespace Contacts;

/**
 * ContactInterface for all `Contact` child classes
 */
interface ContactInterface
{
    public function addFullName($name);

    public function addName($lastName, $firstName, $additionalName, $prefix, $suffix);

    public function addNickname($name);

    public function addPhoto($photo);

    public function addBirthday($year, $month, $day);

    public function addAddress($poBox, $extended, $street, $city, $state, $zip, $country, $type);

    public function addLabel($label, $type);

    public function addTelephone($phone, $type);

    public function addEmail($email, $type);

    public function addMailer($mailer);

    public function addTimeZone($timeZone);

    public function addLatLong($lat, $long);

    public function addTitle($title);

    public function addRole($role);

    public function addLogo($logo);

    public function addAgent($agent);

    public function addOrganization($organization);

    public function addCategories($categories);

    public function addNote($note);

    public function addProductId($productId);

    public function addRevision();

    public function addSortString($sortString);

    public function addSound($sound);

    public function addUniqueIdentifier($uniqueIdentifier);

    public function addUrl($url);

    public function addClassification($classification);

    public function addKey($key);

    public function addExtendedType($label, $value);
}

?>
