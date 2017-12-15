# Changelog

All notable changes (begining with v3.0.3) to `contacts` will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [UNRELEASED]

### Added
- [Read the Docs](https://readthedocs.org) documentation

### Changed
- Daisy-chaining methods allowed
- Tests refactored

### Deprecated
- Nothing

### Removed
- Nothing

### Fixed
- Nothing

### Security
- Nothing

## [Pre-v3.0.3]
### Added
- All the things
- Ability to change the directory the `.vcf` file is saved in, the default time zone, and the default area code (for phone numbers missing an area code) when object is created
- Ability to customize the revision date of the `.vcf` file
- Ability to add photos that are URL-referenced or Base64 encoded (all photos are converted to a Base64 encoding to ensure the photo stays with the contact) 
- Ability to let `contacts` generate an unique ID or to pass your own unique ID for a contact
- iOS and macOS-specific vCard fields. These should theoretically work with any other program that supports the full vCard standard but are not guaranteed to operate in the expected manner on those platforms:
  - Anniversary
  - Spouse
  - Child
  - Supervisor
- CHANGELOG.md that follows [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) principles
- CODE_OF_CONDUCT.md from [Contributor Covenant](http://contributor-covenant.org) v1.4 available at <http://contributor-covenant.org/version/1/4/>
- Github templates:
  - CONTRIBUTING.md that provides guidelines on how to contribute to this project
  - ISSUE_TEMPLATE.md for assisting anyone submitting an issue report
  - PULL_REQUEST_TEMPLATE.md that provides a checklist for how to submit a pull request
- Documentation in the `Documentation` directory using [phpDocumentor](https://www.phpdoc.org)
- Example usage in the `Examples` directory
- Unit tests in the `Test` directory
- `.gitattributes` file to slim-down `composer` installations
- `.styleci.yml` to use [StyleCI](https://styleci.readme.io) to enforce [PSR-2 coding style](http://www.php-fig.org/psr/psr-2/)
- `.travis.yml` to automate tests to make sure builds pass all unit tests

### Changed
- `ContactsException` thrown for invalid input instead of failing silently and falling back to default values

### Deprecated
- Method parameters, such as address types, that could be called with either a delimited string or array, are required to be passed as an array now

### Removed
- Nothing

### Fixed
- Code not adhering to PSR-2 coding standards
- Bugs discovered during testing:
  - Time zone offsets that were not correctly validated
  - Geographic coordinates that were not correctly validated

### Security
- Nothing