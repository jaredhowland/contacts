|Scrutinizer|_ |Build|_ |StyleCI|_ |Packagist|_ |MIT License|_

========
Contacts
========
Contacts is a small PHP library to create files containing address book information for people. Currently, `vCard 3.0 <https://tools.ietf.org/html/rfc2426>`_ is the only supported export format.

========
Features
========
* `vCard 3.0 <https://tools.ietf.org/html/rfc2426>`_ Compatibility:

  * Generates vCards that should be compatible with all applications that use vCards
  * Includes fields that are unique to the iOS and macOS contacts application (``Supervisor``, ``Anniversary``, ``Spouse``, ``Child``)
* Flexible: future implementations will include other formats commonly used to store contact information—other vCard formats, microformats, Google contacts format, ``.csv`` format, etc.
* Method chaining for constructing contact information in a fluid interface

============
Installation
============
Contacts is available as a `Composer <https://getcomposer.org>`_ `package <http://packagist.org/>`_:

1. If needed, install `Composer <https://getcomposer.org>`_
2. Add the following to your ``composer.json`` file

.. code-block:: javascript

   "require": {
      "jaredhowland/contacts": "~6.0"
   }

=====
Usage
=====

Input
-----
This is an extensive example. Most of the time, you will only need a tiny fraction of these fields to create a vCard:

.. code-block:: php

       <?php
          require 'vendor/autoload.php';

          use \Contacts\Options;
          use \Contacts\Vcard;

          // Set desired options
          $options = new Options();
          $options->setDataDirectory('./'); // Tell app where to save `.vcf` file

          $vcard = new Vcard($options);
          $vcard->addFullName('Jane Doe');
          $vcard->addName('Doe', 'Jane');
          $vcard->addNickname('Janie, Jan');
          $vcard->addPhoto('https://raw.githubusercontent.com/jaredhowland/contacts/master/tests/files/photo.jpg');
          $vcard->addBirthday(10, 2, null);
          $vcard->addAddress(null, null, '123 Main', 'Provo', 'UT', '84602', 'United States', ['dom', 'postal', 'parcel', 'work']);
          $vcard->addAddress(null, null, '123 Main', 'Provo', 'UT', '84602', 'United States', ['dom', 'postal', 'parcel', 'home']);
          $vcard->addLabel('Jane Doe\n123 Main St\nProvo, UT 84602', ['dom', 'parcel']);
          $vcard->addTelephone('555-555-5555', ['cell', 'iphone']);
          $vcard->addEmail('jane_doe@domain.com');
          $vcard->addTimeZone('-7');
          $vcard->addLatLong(40.3333331, -111.7777775);
          $vcard->addTitle('System Administrator');
          $vcard->addRole('Programmer');
          // $vcard->addAgent($agent); NOT SUPPORTED
          $vcard->addOrganizations(['Awesome Company']);
          $vcard->addCategories(['School', 'Work']);
          $vcard->addNote('Not much is known about Jane Doe.');
          $vcard->addSortString('Doe');
          // $vcard->addSound($sound); NOT SUPPORTED
          $vcard->addUrl('https://www.example.com');
          // $vcard->addKey($key); NOT SUPPORTED
          $vcard->addAnniversary('2010-10-10');
          $vcard->addSupervisor('Jane Smith');
          $vcard->addSpouse('John Doe');
          $vcard->addChild('Jeff Doe');
          $vcard->addChild('Lisa Doe');
          $vcard->addExtendedType('TWITTER', '@jared_howland');
          $vcard->addUniqueIdentifier();
          $vcard->addRevision('2023-09-05'); // Added automatically if you don't call this method

          $directory->buildVcard(true, 'example');
       ?>

Or you can chain methods together to build the vCard:

.. code-block:: php

        <?php
          require '../vendor/autoload.php';

          use \Contacts\Options;
          use \Contacts\Vcard;

          // Set desired options
          $options = new Options();
          $options->setDataDirectory('./'); // Tell app where to save `.vcf` file

          $vcard = new Vcard($options);
          $vcard->addFullName('Jane Doe')
                ->addName('Doe', 'Jane')
                ->addNickname('Janie, Jan')
                ->addPhoto('https://raw.githubusercontent.com/jaredhowland/contacts/master/tests/files/photo.jpg')
                ->addBirthday(10, 2, null)
                ->addAddress(null, null, '123 Main', 'Provo', 'UT', '84602', 'United States', ['dom', 'postal', 'parcel', 'work'])
                ->addAddress(null, null, '123 Main', 'Provo', 'UT', '84602', 'United States', ['dom', 'postal', 'parcel', 'home'])
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
                ->addUrl('http://www.example.com')
                ->addAnniversary('2010-10-10')
                ->addSupervisor('Jane Smith')
                ->addSpouse('John Doe')
                ->addChild('Jeff Doe')
                ->addChild('Lisa Doe')
                ->addExtendedType('TWITTER', '@jared_howland')
                ->addUniqueIdentifier()
                ->addRevision('2023-09-05') /* Added automatically with the current date and time if you don't call this method */
                ->buildVcard(true, 'example');
          // $vcard->addAgent($agent); NOT SUPPORTED
          // $vcard->addSound($sound); NOT SUPPORTED
          // $vcard->addKey($key); NOT SUPPORTED
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
   CATEGORIES:School,Work
   NOTE:Not much is known about Jane Doe.
   SORT-STRING:Doe
   URL:https://www.example.com
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
   REV:2023-09-05T00:00:00Z
   END:VCARD

Options
-------

Available options and defaults in the ``Options`` class:

- ``setDataDirectory``: ``./data/``
- ``setDefaultAreaCode``: ``null``
- ``setFormatUsTelephone``: ``true``

With appropriate getters:

- ``getDataDirectory()``
- ``getDefaultAreaCode()``
- ``isFormatUsTelephone()``

==========
Contribute
==========
* Issue Tracker: https://github.com/jaredhowland/contacts/issues
* Source Code: https://github.com/jaredhowland/contacts
* Code of conduct: https://github.com/jaredhowland/contacts/blob/master/CODE_OF_CONDUCT.rst

============
Known Issues
============

- Date-time values not supported for ``BDAY`` field (only date values). No plans to implement.
- Text values not supported for ``TZ`` field (only UTC-offset values). No plans to implement.
- The following vCard elements are not currently supported (no plans to implement):

  - ``AGENT``
  - ``SOUND``
  - ``KEY``

Inspired by https://github.com/jeroendesloovere/vcard

.. |Scrutinizer| image:: https://img.shields.io/scrutinizer/g/jaredhowland/contacts/main.svg?style=flat-square
.. _Scrutinizer: https://scrutinizer-ci.com/g/jaredhowland/contacts/

.. |Build| image:: https://img.shields.io/scrutinizer/build/g/jaredhowland/contacts/main.svg?style=flat-square
.. _Build: https://scrutinizer-ci.com/g/jaredhowland/contacts/

.. |StyleCI| image:: https://styleci.io/repos/71304265/shield?branch=main
.. _StyleCI: https://styleci.io/repos/71304265

.. |Packagist| image:: https://img.shields.io/packagist/v/jaredhowland/contacts.svg?style=flat-square
.. _Packagist: https://packagist.org/packages/jaredhowland/contacts

.. |MIT License| image:: https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square
.. _MIT License: LICENSE.rst
