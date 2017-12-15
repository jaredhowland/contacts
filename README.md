[![Latest Version on Packagist][ico-version]][https://packagist.org/packages/jaredhowland/contacts]
[![Build Status](https://travis-ci.org/jaredhowland/contacts.svg?branch=master)](https://travis-ci.org/jaredhowland/contacts)
[![Software License][ico-license]](LICENSE)
[![Total Downloads][ico-downloads]][https://packagist.org/packages/jaredhowland/contacts/stats]

About Contacts
==============
Contacts is a small PHP library to create files containing address book information for contacts. Currently, [vCard 3.0][3] is the only supported export format.

Installation
============
Contacts is available as a [Composer][1] [package][2].

Composer
--------
1. If needed, install [Composer][1]
2. Add the following to your `composer.json` file:
```json
"require": {
  "jaredhowland/contacts": "~3.0"
}
```

Usage
=====
```php
$vcard = new \Contacts\Vcard('./'); // Tell contacts where to put the `.vcf` file
$vcard->addFullName('Jane Doe');
$vcard->addName('Doe', 'Jane');
$vcard->addNicknames(['Janie', 'Jan']);
$vcard->addPhoto('https://raw.githubusercontent.com/jaredhowland/contacts/master/Test/files/photo.jpg');
$vcard->addBirthday(null, 2, 10);
$vcard->addAddress(null, null, '123 Main St', 'Provo', 'UT', '84602', 'United States', ['dom', 'postal', 'parcel', 'work']);
$vcard->addAddress(null, null, '123 Main St', 'Provo', 'UT', '84602', 'United States', ['dom', 'postal', 'parcel', 'home']);
$vcard->addLabel('Jane Doe\n123 Main St\nProvo, UT 84602', ['dom', 'parcel']);
$vcard->addTelephone('555-555-5555', ['cell', 'iphone']);
$vcard->addEmail('jane_doe@domain.com');
$vcard->addTimeZone('-7');
$vcard->addLatLong(40.3333331, -111.7777775);
$vcard->addTitle('System Administrator');
$vcard->addRole('Programmer');
// $vcard->addAgent($agent); NOT SUPPORTED
$vcard->addOrganizations(['Awesome Company']);
$vcard->addCategories(['School,Work']);
$vcard->addNote('Not much is known about Jane Doe.');
$vcard->addSortString('Doe');
// $vcard->addSound($sound); NOT SUPPORTED
$vcard->addUrl('http://www.example.com');
// $vcard->addKey($key); NOT SUPPORTED
$vcard->addAnniversary('2010-10-10');
$vcard->addSupervisor('Jane Smith');
$vcard->addSpouse('John Doe');
$vcard->addChild('Jeff Doe');
$vcard->addChild('Lisa Doe');
$vcard->addExtendedType('TWITTER', '@jared_howland');
$vcard->addUniqueIdentifier();
$vcard->addRevision('2017-12-14'); // Added automatically if you don't call this method
$vcard->buildVcard(true, 'myVcard'); // Bool tells whether to save the file to a directory or not (`false` is default`)
```

Output
------
```
BEGIN:VCARD
VERSION:3.0
FN:Jane Doe
N:Doe;Jane;;;
NICKNAME:Janie,Jan
PHOTO;ENCODING=b;TYPE=JPEG:/9j/4QBwRXhpZgAASUkqAAgAAAABAJiCAgBLAAAAGgAAAA
 AAAABDb3B5cmlnaHQgQllVIFB …rest of binary-encoded photo
BDAY;X-APPLE-OMIT-YEAR=1604:1604-02-10
ADR;TYPE=dom,postal,parcel,work:;;123 Main St;Provo;UT;84602;United State
 s
ADR;TYPE=dom,postal,parcel,home:;;123 Main St;Provo;UT;84602;United State
 s
LABEL;TYPE=dom,parcel:Jane Doe\n123 Main St\nProvo\, UT 84602
TEL;TYPE=cell,iphone:(555) 555-5555
EMAIL;TYPE=internet:jane_doe@domain.com
TZ:-07:00
GEO:40.333333;-111.777778
TITLE:System Administrator
ROLE:Programmer
ORG:Awesome Company
CATEGORIES:School\,Work
NOTE:Not much is known about Jane Doe.
SORT-STRING:Doe
URL:http://www.example.com
item1.X-ABDATE;type=pref:2010-10-10
item1.X-ABLabel:_$!<Anniversary>!$_
item2.X-ABRELATEDNAMES:Jane Smith
item2.X-ABLabel:_$!<Manager>!$_
item3.X-ABRELATEDNAMES:John Doe
item3.X-ABLabel:_$!<Spouse>!$_
item4.X-ABRELATEDNAMES:Jeff Doe
item4.X-ABLabel:_$!<Child>!$_
item5.X-ABRELATEDNAMES:Lisa Doe
item5.X-ABLabel:_$!<Child>!$_
X-TWITTER:@jared_howland
UID:5a32a74023b097.12918287
REV:2017-12-14T00:00:00Z
END:VCARD


```

Known Issues
============
  * Date-time values not supported for `BDAY` field (only date values). No plans to implement.
  * Text values not supported for `TZ` field (only UTC-offset values). No plans to implement.
  * The following vCard elements are not currently supported (no plans to implement):
      * `AGENT`
      * `SOUND`
      * `KEY`

Inspired by https://github.com/jeroendesloovere/vcard

[1]: https://getcomposer.org
[2]: http://packagist.org/
[3]: https://tools.ietf.org/html/rfc2426