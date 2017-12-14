[![Build Status](https://travis-ci.org/jaredhowland/contacts.svg?branch=master)](https://travis-ci.org/jaredhowland/contacts)

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
$vcard = new vcard;
$vcard->addFullName('Jane Doe');
$vcard->addName('Doe', 'Jane');
$vcard->addNickname('Janie');
$vcard->addPhoto('http://images.designntrend.com/data/images/full/77769/jane-doe.jpg?w=780');
$vcard->addBirthday(null, 2, 10);
$vcard->addAddress(null, null, '123 Main St', 'Provo', 'UT', '84602', 'United States', 'dom,postal,parcel,work');
$vcard->addAddress(null, null, '123 Main St', 'Provo', 'UT', '84602', 'United States', 'dom,postal,parcel,home');
$vcard->addLabel('Jane Doe\n123 Main St\nProvo, UT 84602', 'dom,parcel');
$vcard->addTelephone('555-555-5555', 'cell,iphone');
$vcard->addEmail('jane_doe@domain.com');
$vcard->addTimeZone('-7');
$vcard->addLatLong(40.3333331, -111.7777775);
$vcard->addTitle('System Administrator');
$vcard->addRole('Programmer');
$vcard->addOrganization('Awesome Company');
$vcard->addCategories('School,Work');
$vcard->addNote('Not much is known about Jane Doe.');
$vcard->addSortString('Doe');
$vcard->addUrl('http://www.example.com');
$vcard->addAnniversary('2010-10-10');
$vcard->addSupervisor('Jane Smith');
$vcard->addSpouse('John Doe');
$vcard->addChild('Jeff Doe');
$vcard->addChild('Lisa Doe');
$contact = $vcard->buildVcard();
echo $contact;
```

Output
------
```
BEGIN:VCARD
VERSION:3.0
FN:Jane Doe
N:Doe;Jane;;;
NICKNAME:Janie
PHOTO;ENCODING=b;TYPE=jpeg:/9j/4AAQSkZJRgABAQAAAQABAAD//gA8Q1JFQVRPUjogZ2
 QtanBlZyB2MS4wICh1c2luZyBJSkcgSlBFRyB2ODApLCBxdWFsaXR5ID0gMTAwCv/bAEMAAQE
 …rest of binary-encoded photo
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
CATEGORIES:School,Work
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
REV:2016-10-18T14:55:15Z
END:VCARD
```

Known Issues
============
  * Date-time values not supported for `BDAY` field (only date values). No plans to implement.
  * Text values not supported for `TZ` field (only UTC-offset values). No plans to implement.
  * Binary photo data not supported for `PHOTO` field (URL-referenced values only). No plans to implement.
  * Binary photo data not supported for `LOGO` field (URL-referenced values only). No plans to implement.
  * The following vCard elements are not currently supported (no plans to implement):
      * `AGENT`
      * `SOUND`
      * `KEY`

Inspired by https://github.com/jeroendesloovere/vcard

[1]: https://getcomposer.org
[2]: http://packagist.org/
[3]: https://tools.ietf.org/html/rfc2426
