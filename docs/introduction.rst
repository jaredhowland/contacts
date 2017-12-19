############
Introduction
############
Managing contact information can be difficult. Moving contact information across applications and platforms can be even more difficult. Contacts is a small PHP library to help manage contacts in various formats. Currently, only vCard 3.0 is supported.

Features
--------
* `vCard 3.0 <https://tools.ietf.org/html/rfc2426>`_ Compatibility:

  * Generates vCards that should be compatible with all applications that use vCards
  * Includes fields that are unique to the iOS and macOS contacts application (``Supervisor``, ``Anniversary``, ``Spouse``, ``Child``)
* Flexible: future implementations will include other formats commonly used to store contact information—other vCard formats, microformats, Google contacts format, ``.csv`` format, etc.
* Method chaining for constructing contact information in a fluid interface