<?php

use Contacts\Options;
use Contacts\Vcard;

require_once '../vendor/autoload.php';

$options = new Options();
$options->dataDirectory('./');

$directory = new Vcard($options);
$directory->addFullName('Jane Doe');
$directory->addName('Doe', 'Jane');
$directory->addNicknames(['Janie', 'Jan']);
$directory->addPhoto('https://raw.githubusercontent.com/jaredhowland/contacts/master/tests/files/photo.jpg');
$directory->addBirthday(2, 10);
$directory->addAddress(
    null,
    null,
    '123 Main St',
    'Provo',
    'UT',
    '84602',
    'United States',
    ['dom', 'postal', 'parcel', 'work']
);
$directory->addAddress(
    null,
    null,
    '123 Main St',
    'Provo',
    'UT',
    '84602',
    'United States',
    ['dom', 'postal', 'parcel', 'home']
);
$directory->addLabel('Jane Doe\n123 Main St\nProvo, UT 84602', ['dom', 'parcel']);
$directory->addTelephone('555-555-5555', ['cell', 'iphone']);
$directory->addEmail('jane_doe@domain.com');
$directory->addTimeZone('-7');
$directory->addLatLong(40.3333331, -111.7777775);
$directory->addTitle('System Administrator');
$directory->addRole('Programmer');
// $directory->addAgent($agent); NOT SUPPORTED
$directory->addOrganizations(['Awesome Company']);
$directory->addCategories(['School', 'Work']);
$directory->addNote('Not much is known about Jane Doe.');
$directory->addSortString('Doe');
// $directory->addSound($sound); NOT SUPPORTED
$directory->addUrl('https://www.example.com');
// $directory->addKey($key); NOT SUPPORTED
$directory->addAnniversary('2010-10-10');
$directory->addSupervisor('Jane Smith');
$directory->addSpouse('John Doe');
$directory->addChild('Jeff Doe');
$directory->addChild('Lisa Doe');
$directory->addExtendedType('TWITTER', '@jared_howland');
$directory->addUniqueIdentifier();
$directory->addRevision('2023-09-04'); // Added automatically if you don't call this method

$directory->buildVcard(
    true,
    'example'
); // Writes to `./data/` directory by default unless you set a different directory when you create a new `Contacts` object
