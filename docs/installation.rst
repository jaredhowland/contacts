############
Installation
############
You can download Contacts and include it in your application or install it using `Composer <https://getcomposer.org>`_.

.. note::
    It is strongly recommended that you install by using the Contacts `Composer <https://getcomposer.org>`_ `package <http://packagist.org/>`_.

Composer
--------
If needed, install `Composer <https://getcomposer.org>`_.

Composer in $PATH
^^^^^^^^^^^^^^^^^
If Composer is included in your ``$PATH``, navigate to your app's root directory from the terminal and enter the following:

.. code-block:: bash

    php composer require jaredhowland/contacts

Composer Not in $PATH
^^^^^^^^^^^^^^^^^^^^^
If you are using the Composer ``.phar`` and have not included it in your ``$PATH``, navigate to your app's root directory from the terminal and enter the following:

.. code-block:: bash

    php path/to/composer.phar require jaredhowland/contacts

Composer JSON
^^^^^^^^^^^^^
You can also just add the following to your app's ``composer.json`` file:

.. code-block:: javascript

   "require": {
      "jaredhowland/contacts": "~4.0"
   }

Local Download
--------------
.. note::
    It is strongly recommended that you install by using the Contacts `Composer <https://getcomposer.org>`_ `package <http://packagist.org/>`_.

If you have downloaded Contacts and want to include it in your application, include the following in your application:

.. code-block:: php

    <?php
        require_once 'path/to/src/Contacts/Config.php';
        require_once 'path/to/src/Contacts/ContactsException.php';
        require_once 'path/to/src/Contacts/Contacts.php';
        require_once 'path/to/src/Contacts/ContactsInterface.php';
        require_once 'path/to/src/Contacts/vCard.php';

        …do stuff
    ?>