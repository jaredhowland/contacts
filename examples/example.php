<?php

use Contacts\Vcard;

require_once '../vendor/autoload.php';

$directory = new Vcard('./');
$directory->addFullName('Jane Doe');
$directory->addName('Doe', 'Jane');
$directory->addNicknames(['Janie', 'Jan']);
$directory->addPhoto('https://github.com/jaredhowland/contacts/raw/master/tests/files/photo.jpg');
$directory->addBirthday(2, 10);
$directory->addAddress(['dom', 'postal', 'parcel', 'work'], null, null, '123 Main St', 'Provo', 'UT', '84602', 'United States');
$directory->addAddress(['dom', 'postal', 'parcel', 'home'], null, null, '123 Main St', 'Provo', 'UT', '84602', 'United States');
$directory->addLabel(['dom', 'parcel'], 'Jane Doe\n123 Main St\nProvo, UT 84602');
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
$directory->addUrl('http://www.example.com');
// $directory->addKey($key); NOT SUPPORTED
$directory->addAnniversary('2010-10-10');
$directory->addSupervisor('Jane Smith');
$directory->addSpouse('John Doe');
$directory->addChild('Jeff Doe');
$directory->addChild('Lisa Doe');
$directory->addExtendedType('TWITTER', '@jared_howland');
$directory->addUniqueIdentifier();
$directory->addRevision('2017-12-14'); // Added automatically if you don't call this method

$directory->buildVcard(true, 'example'); // Writes to `./data/` directory by default unless you set a different directory when you create a new `Contacts` object
