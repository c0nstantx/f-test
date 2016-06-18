<?php

namespace Foodora\DB;
use Foodora\DB\Mysql\Mysql;
use Foodora\DB\Mysql\Mysqli;
use Foodora\DB\Mysql\PDO;

/**
 * Database factory
 *
 * @author K. Christofilos <kostas.christofilos@gmail.com>
 */
class DBFactory
{
    /** @var DBInterface */
    protected static $db;

    /**
     * Get a DB singleton object, except explicitly defined for a new instance
     * 
     * @param array $options
     * @param bool $newInstance
     * 
     * @return DBInterface
     */
    public static function getDB(array $options, $newInstance = false)
    {
        if ($newInstance) {
            return self::build($options);
        }
        
        if (null === self::$db) {
            self::$db = self::build($options);
        }

        return self::$db;
    }

    /**
     * Build a DB object based on available extensions
     *
     * @param array $options
     *
     * @return DBInterface
     */
    protected static function build(array $options)
    {
        if (class_exists('\PDO')) {
            return new PDO($options);
        } else if (class_exists('\mysqli')) {
            return new Mysqli($options);
        } else {
            return new Mysql($options);
        }
    }
}