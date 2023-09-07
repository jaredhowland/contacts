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
    private string $dataDirectory = './data/';
    private ?string $defaultAreaCode = null;
    private bool $formatUsTelephone = true;

    /**
     * Set the data directory path to save to
     *
     * @param string $dataDirectory Path to data directory
     * @return $this
     */
    public function setDataDirectory(string $dataDirectory): Options
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
    public function setDefaultAreaCode(string $defaultAreaCode): Options
    {
        $this->defaultAreaCode = $defaultAreaCode;
        return $this;
    }

    /**
     * Set whether to format as U.S. phone number
     *
     * @param bool $formatUsTelephone
     * @return $this
     */
    public function setFormatUsTelephone(bool $formatUsTelephone): Options
    {
        $this->formatUsTelephone = $formatUsTelephone;
        return $this;
    }

    /**
     * Get the data directory to save files to
     *
     * @return string Path to data directory
     */
    public function getDataDirectory(): string
    {
        return $this->dataDirectory;
    }

    /**
     * Get default area code
     *
     * @return string|null Default area code or `null` if not set
     */
    public function getDefaultAreaCode(): ?string
    {
        return $this->defaultAreaCode;
    }

    /**
     * Get whether to format as U.S. phone number
     *
     * @return bool
     */
    public function isFormatUsTelephone(): bool
    {
        return $this->formatUsTelephone;
    }
}
