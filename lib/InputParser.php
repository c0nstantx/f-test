<?php

namespace Foodora;

/**
 * Input parser for command input arguments
 *
 * @author K. Christofilos <kostas.christofilos@gmail.com>
 */
class InputParser
{
    protected $arguments = array();

    /**
     * Parse input arguments
     *
     * @param array $argv
     */
    public function parse(array $argv)
    {
        if (in_array('--help', $argv)) {
            echo $this->getHelp();

            exit(0);
        }

        for ($i=1; $i<count($argv); $i+=2) {
            $this->arguments[str_replace('--', '', $argv[$i])] = $argv[$i+1];
        }
    }

    /**
     * Return the value of input argument
     *
     * @param string $arg
     * 
     * @return array
     */
    public function getArgument($arg)
    {
        if (!$this->hasArgument($arg)) {
            echo "Argument '--$arg' wasn't defined. Please see help\n\n";
            echo $this->getHelp();

            exit(-1);
        }

        return $this->arguments[$arg];
    }

    /**
     * Returns if an argument was defined
     *
     * @param string $arg
     *
     * @return bool
     */
    public function hasArgument($arg)
    {
        return isset($this->arguments[$arg]);
    }

    /**
     * Get command's help
     *
     * @return string
     */
    protected function getHelp()
    {
        return
<<<EOD
    Special days switcher
    
    This script switches normal with special days. 
    It takes a date range and/or vendor id as arguments.

    --date                  Date. Define the day for switch (See here for supported formats: http://php.net/manual/en/datetime.formats.php)
    --vendor[optional]      Vendor ID. Apply the range for a specific vendor.
    --help                  Display this message.
    \n
EOD;

    }
}