<?php

namespace Foodora\Model;

/**
 * Input parser for command input arguments
 *
 * @author K. Christofilos <kostas.christofilos@gmail.com>
 */
class InputParser
{
    /** @var array */
    protected $arguments = array();

    /** @var string */
    protected $helpText;

    public function __construct($helpText)
    {
        $this->helpText = $helpText;
    }

    /**
     * Parse input arguments
     *
     * @param array $argv
     */
    public function parse(array $argv)
    {
        if (in_array('--help', $argv)) {
            echo $this->helpText;

            exit(0);
        }

        for ($i=1; $i<count($argv); $i+=2) {
            if ($argv[$i] === '--restore') {
                $this->arguments[str_replace('--', '', $argv[$i])] = true;
                $i--;
            } else {
                $this->arguments[str_replace('--', '', $argv[$i])] = $argv[$i+1];
            }
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
            echo $this->helpText;

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
}