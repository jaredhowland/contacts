.. image:: https://img.shields.io/travis/jaredhowland/contacts/dev.svg?style=flat-square :target: https://travis-ci.org/jaredhowland/contacts
.. image:: https://img.shields.io/scrutinizer/g/jaredhowland/contacts.svg?style=flat-square   :target: https://scrutinizer-ci.com/g/jaredhowland/contacts/
.. image:: https://styleci.io/repos/71304265/shield?branch=dev   :target: https://styleci.io/repos/71304265
.. image:: https://img.shields.io/packagist/v/jaredhowland/contacts.svg?style=flat-square   :target: https://packagist.org/packages/jaredhowland/contacts
.. image:: https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square   :target: LICENSE.rst

==============
About Contacts
==============
Contacts is a small PHP library to create files containing address book information. Currently, `vCard 3.0 <https://tools.ietf.org/html/rfc2426>`_ is the only supported export format. Future implementations may add other vCard formats, microformats, Google contacts format, ``.csv`` format, etc.

vCard can be a difficult format to work with. The `full vCard 3.0 standard <https://tools.ietf.org/html/rfc2426>`_ is sometimes vague which has lead to inconsistent interpretations across different applications over the years. This library should generate vCard files that can be used across most applications that support the vCard format. It is especially helpful for generating contact information that can be used for iOS and macOS contact applications.

============
Installation
============
Contacts is available as a `Composer <https://getcomposer.org>`_ `package <http://packagist.org/>`_.

Composer
--------
1. If needed, install `Composer <https://getcomposer.org>`_
2. Add the following to your ``composer.json`` file

.. code-block:: javascript

   "require": {
      "jaredhowland/contacts": "~3.0"
   }

=====
Usage
=====

.. code-block:: php

       <?php
          require_once '../vendor/autoload.php';

          $directory = new \Contacts\Vcard('./');
          $directory->addFullName('Jane Doe');
          $directory->addName('Doe', 'Jane');
          $directory->addNicknames(['Janie', 'Jan']);
          $directory->addPhoto('https://raw.githubusercontent.com/jaredhowland/contacts/master/Test/files/photo.jpg');
          $directory->addBirthday(null, 2, 10);
          $directory->addAddress(null, null, '123 Main', 'Provo', 'UT', '84602', 'United States', ['dom', 'postal', 'parcel', 'work']);
          $directory->addAddress(null, null, '123 Main', 'Provo', 'UT', '84602', 'United States', ['dom', 'postal', 'parcel', 'home']);
          $directory->addLabel('Jane Doe\n123 Main St\nProvo, UT 84602', ['dom', 'parcel']);
          $directory->addTelephone('555-555-5555', ['cell', 'iphone']);
          $directory->addEmail('jane_doe@domain.com');
          $directory->addTimeZone('-7');
          $directory->addLatLong(40.3333331, -111.7777775);
          $directory->addTitle('System Administrator');
          $directory->addRole('Programmer');
          // $directory->addAgent($agent); NOT SUPPORTED
          $directory->addOrganizations(['Awesome Company']);
          $directory->addCategories(['School,Work']);
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
       ?>

Output
------

.. code-block:: none

   BEGIN:VCARD
   VERSION:3.0
   FN:Jane Doe
   N:Doe;Jane;;;
   NICKNAME:Janie,Jan
   PHOTO;ENCODING=b;TYPE=JPEG:/9j/4QBwRXhpZgAASUkqAAgAAAABAJiCAgBLAAAAGgAAAA
    AAAABDb3B5cmlnaHQgQllVIFB …rest of binary-encoded photo
   BDAY;X-APPLE-OMIT-YEAR=1604:1604-02-10
   ADR;TYPE=dom,postal,parcel,work:;;123 Main;Provo;UT;84602;United States
   ADR;TYPE=dom,postal,parcel,home:;;123 Main;Provo;UT;84602;United States
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

Known Issues
============

- Date-time values not supported for ``BDAY`` field (only date values). No plans to implement.
- Text values not supported for ``TZ`` field (only UTC-offset values). No plans to implement.
- The following vCard elements are not currently supported (no plans to implement):

  - ``AGENT``
  - ``SOUND``
  - ``KEY``

Inspired by https://github.com/jeroendesloovere/vcard
