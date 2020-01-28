All notable changes (beginning with v3.0.3) to ``contacts`` will be documented in this file.

The format is based on `Keep a Changelog <http://keepachangelog.com/en/1.0.0/>`_
and this project adheres to `Semantic Versioning <http://semver.org/spec/v2.0.0.html>`_.

************
[UNRELEASED]
************

Added
-----

- Nothing

Changed
-------

- ``addLabel`` method so ``types`` parameter is now required just like it is in ``addAddress``

Deprecated
----------

- Nothing

Removed
-------

- Nothing

Fixed
-----

- Nothing

************
[v5.0.0]
************

Added
-----

- Nothing

Changed
-------

- Behavior of ``addAddress`` and ``addLabel`` (see Fixed section)

Deprecated
----------

- Nothing

Removed
-------

- Nothing

Fixed
-----

- ``addAddress`` method so optional parameters come after required parameter ``types``
- ``addLabel`` method to match ``addAddress`` in having ``types`` parameter before other parameters
- ``CHANGELOG.rst`` file formatting problems
- ``testAddAddress`` and ``testAddLabel`` tests to match new behaviors

************
[v4.1.0]
************

Added
-----

- Nothing

Changed
-------

- Reduced code complexity
- Date files were edited

Deprecated
----------

- Nothing

Removed
-------

- Nothing

Fixed
-----

- ``addBirthday`` method so optional ``year`` parameter comes after required methods parameters ``month`` and ``day``
- ``testAddBirthdayWithYear`` so test passes again
- ``testAddBirthdayWithoutYear`` so test passes again

Security
--------

- Nothing

************
[v4.0.0]
************

Added
-----

- `Read the Docs <https://readthedocs.org>`_ documentation

Changed
-------

- Daisy-chaining methods allowed
- Tests refactored
- ``Vcard.php`` helpers moved to another class (``Helpers.php``)
- ``PSR-12`` formatted code

Deprecated
----------

- Nothing

Removed
-------

- ``Contacts`` subdirectory from ``src``

Fixed
-----

- Nothing

Security
--------

- ``PHP 7.2`` and above now required

************
[Pre-v3.0.3]
************

Added
-----

- All the things
- Ability to change the directory the ``.vcf`` file is saved in, the default time zone, and the default area code (for phone numbers missing an area code) when object is created
- Ability to customize the revision date of the ``.vcf`` file
- Ability to add photos that are URL-referenced or Base64 encoded (all photos are converted to a Base64 encoding to ensure the photo stays with the contact) 
- Ability to let ``contacts`` generate an unique ID or to pass your own unique ID for a contact
- iOS and macOS-specific vCard fields. These should theoretically work with any other program that supports the full vCard standard but are not guaranteed to operate in the expected manner on those platforms:

  - Anniversary
  - Spouse
  - Child
  - Supervisor
- CHANGELOG.md that follows `Keep a Changelog <http://keepachangelog.com/en/1.0.0/>`_ principles
- CODE_OF_CONDUCT.md from `Contributor Covenant <http://contributor-covenant.org>`_ v1.4 available at http://contributor-covenant.org/version/1/4/
- Github templates:

  - CONTRIBUTING.md that provides guidelines on how to contribute to this project
  - ISSUE_TEMPLATE.md for assisting anyone submitting an issue report
  - PULL_REQUEST_TEMPLATE.md that provides a checklist for how to submit a pull request
- Documentation in the ``phpdocs`` directory using `phpDocumentor <https://www.phpdoc.org>`_
- Example usage in the ``examples`` directory
- Unit tests in the ``tests`` directory
- ``.gitattributes`` file to slim-down ``composer`` installations
- ``.styleci.yml`` to use `StyleCI <https://styleci.readme.io>`_ to enforce `PSR-2 coding style <http://www.php-fig.org/psr/psr-2/>`_
- ``.travis.yml`` to automate tests to make sure builds pass all unit tests

Changed
-------

- ``ContactsException`` thrown for invalid input instead of failing silently and falling back to default values

Deprecated
----------

- Method parameters, such as address types, that could be called with either a delimited string or array, are required to be passed as an array now

Removed
-------

- Nothing

Fixed
-----

- Code not adhering to PSR-2 coding standards
- Bugs discovered during testing:

  - Time zone offsets that were not correctly validated
  - Geographic coordinates that were not correctly validated

Security
--------

- Nothing