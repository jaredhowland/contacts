<?php
/**
 * Options for `Vcard` class
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @version 2023-09-04
 * @since   2023-09-04
 *
 */

namespace Contacts;

class Options
{
    public string $dataDirectory = './data/';
    public ?string $defaultAreaCode = null;
    public bool $formatUsTelephone = true;

    /**
     * Set the data directory path to save to
     *
     * @param string $dataDirectory Path to data directory
     * @return $this
     */
    public function dataDirectory(string $dataDirectory): Options
    {
        $this->dataDirectory = $dataDirectory;
        return $this;
    }

    /**
     * Set default area code to use
     *
     * @param string $defaultAreaCode
     * @return $this
     */
    public function defaultAreaCode(string $defaultAreaCode): Options
    {
        $this->defaultAreaCode = $defaultAreaCode;
        return $this;
    }

    public function formatUsTelephone(bool $formatUsTelephone): Options
    {
        $this->formatUsTelephone = $formatUsTelephone;
        return $this;
    }
}
