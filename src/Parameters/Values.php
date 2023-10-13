<?php

declare(strict_types=1);
/**
 * Values.php
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @since   2023-10-08
 * @license https://opensource.org/license/mit/ MIT
 */

namespace Contacts\Parameters;

/**
 * **RFC 6350, Section 5.2, pp. 16–17**
 *
 * The `VALUE` parameter is `OPTIONAL`, used to identify the value type
 * (data type) and format of the value. The use of these predefined
 * formats is encouraged even if the value parameter is not explicitly
 * used. By defining a standard set of value types and their formats,
 * existing parsing and processing code can be leveraged. The
 * predefined data type values `MUST NOT` be repeated in `COMMA`-separated
 * value lists except within the `N`, `NICKNAME`, `ADR`, and `CATEGORIES`
 * properties.
 *
 * *Usage*: `VALUE=value-type`
 *
 * *Example*: `BDAY;VALUE=text:circa 1800`
 */
class Values
{
    /*
     * **RFC 6350, Section 4.1, p. 11**
     *
     * The 'text' value type should be used to identify values that
     * contain human-readable text. As for the language, it is controlled
     * by the `LANGUAGE` property parameter defined in Section 5.1.
     *
     * Examples for 'text':
     *
     * * `this is a text value`
     * * `this is one value,this is another`
     * * `this is a single value\, with a comma encoded`
     *
     * A formatted text line break in a text value type `MUST` be represented
     * as the character sequence backslash (`U+005C`) followed by a Latin
     * small letter `n` (`U+006E`) or a Latin capital letter `N` (`U+004E`), that
     * is, "`\n`" or "`\N`".
     *
     * For example, a multiple line `NOTE` value of:
     *
     * `Mythical Manager`
     *
     * `Hyjinx Software Division`
     *
     * `BabsCo, Inc.`
     *
     * could be represented as:
     *
     * `NOTE:Mythical Manager\nHyjinx Software Division\n
     *  BabsCo\, Inc.\n`
     *
     * demonstrating the `\n` literal formatted line break technique, the
     * `CRLF`-followed-by-space line folding technique, and the backslash
     * escape technique.
     */
    protected const TEXT = 'text';
    protected const URI = 'uri';
    protected const DATE = 'date';
    protected const TIME = 'time';
    protected const DATE_TIME = 'date-time';
    protected const DATE_AND_OR_TIME = 'date-and-or-time';
    protected const TIMESTAMP = 'timestamp';
    protected const BOOLEAN = 'boolean';
    protected const INTEGER = 'integer';
    protected const FLOAT = 'float';
    protected const UTC_OFFSET = 'utc-offset';
    protected const LANGUAGE_TAG = 'language-tag';
    protected const IANA_TOKEN = 'iana-token';
    protected const X_NAME = 'x-name';
}
