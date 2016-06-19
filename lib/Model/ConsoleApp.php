<?php

namespace Foodora\Model;

/**
 * Main Application for console commands
 *
 * @author K. Christofilos <kostas.christofilos@gmail.com>
 */
class ConsoleApp
{
    const DEFAULT_MIN_PHP_VERSION = '5.3.0';

    protected $options = array();

    protected $container = array();

    public function __construct(array $options)
    {
        $this->options = $options;

    }

    /**
     * Boot up the application
     *
     * @param array $argv
     *
     * @return int
     */
    public function boot(array $argv)
    {
        $this->checkMinVersion();
        try {
            $this->container['db'] = \Foodora\DB\DBFactory::getDB($this->options['db']);
        } catch (\Exception $ex) {
            echo "Connection to database failed: {$ex->getMessage()}\n";

            return -1;
        }

        $helpText = isset($this->options['help_text']) ? $this->options['help_text'] : '';
        $this->container['input_parser'] = new InputParser($helpText);
        $this->container['input_parser']->parse($argv);

        return 0;
    }

    /**
     * Returns a specific dependency service from the container
     *
     * @param string $dependency
     *
     * @return mixed
     */
    public function get($dependency)
    {
        if ($this->has($dependency)) {
            return $this->container[$dependency];
        }

        throw new \RuntimeException("Dependency '$dependency' could not be found in the container");
    }

    /**
     * Returns if a specific dependency service exist in the container
     *
     * @param string $dependency
     *
     * @return bool
     */
    public function has($dependency)
    {
        return isset($this->container[$dependency]);
    }

    protected function checkMinVersion()
    {
        if (!isset($this->options['php_min'])) {
            $this->options['php_min'] = self::DEFAULT_MIN_PHP_VERSION;
        }

        if ($this->options['php_min'] > PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION) {
            echo "Minimum PHP version required is: {$this->options['php_min']}\n";

            return -1;
        }
    }
}