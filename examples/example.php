<?php

declare(strict_types=1);

use Contacts\Options;
use Contacts\Vcard;

require_once '../vendor/autoload.php';

// Delete old `example.vcf` vCard
if (file_exists('example.vcf')) {
    unlink('example.vcf');
}

// Set desired options for the `Vcard` class
$options = new Options();
$options->setDataDirectory('./');

// Create the vCard and save as `example.vcf`
$directory = new Vcard($options);
$directory->addFullName('Jane Doe')
    ->addName('Doe', 'Jane')
    ->addNickname('Janie, Jan')
    ->addPhoto('https://raw.githubusercontent.com/jaredhowland/contacts/master/tests/files/photo.jpg')
    ->addBirthday(2, 10)
    ->addAddress(
        null,
        null,
        '123 Main St',
        'Provo',
        'UT',
        '84602',
        'United States',
        ['dom', 'postal', 'parcel', 'work']
    )
    ->addAddress(
        null,
        null,
        '123 Main St',
        'Provo',
        'UT',
        '84602',
        'United States',
        ['dom', 'postal', 'parcel', 'home']
    )
    ->addLabel('Jane Doe\n123 Main St\nProvo, UT 84602', ['dom', 'parcel'])
    ->addTelephone('555-555-5555', ['cell', 'iphone'])
    ->addEmail('jane_doe@domain.com')
    ->addTimeZone('-7')
    ->addLatLong(40.3333331, -111.7777775)
    ->addTitle('System Administrator')
    ->addRole('Programmer')
    ->addOrganizations(['Awesome Company'])
    ->addCategories(['School', 'Work'])
    ->addNote('Not much is known about Jane Doe.')
    ->addSortString('Doe')
    ->addUrl('https://www.example.com')
    ->addAnniversary('2010-10-10')
    ->addSupervisor('Jane Smith')
    ->addSpouse('John Doe')
    ->addChild('Jeff Doe')
    ->addChild('Lisa Doe')
    ->addExtendedType('TWITTER', '@jared_howland')
    ->addUniqueIdentifier()
    ->addRevision('2023-09-10')
    ->buildVcard(
        true,
        'example'
    );
// $directory->addAgent($agent); NOT SUPPORTED
// $directory->addSound($sound); NOT SUPPORTED
// $directory->addKey($key); NOT SUPPORTED
