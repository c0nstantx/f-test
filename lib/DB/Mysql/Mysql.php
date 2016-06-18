<?php

namespace Foodora\DB\Mysql;
use Foodora\DB\DBInterface;

/**
 * Database driver for Mysql
 *
 * @author K. Christofilos <kostas.christofilos@gmail.com>
 * @link http://php.net/manual/en/book.mysql.php
 */
class Mysql implements DBInterface
{
    public function __construct(array $options)
    {
        $this->connect($options);
    }
    
    public function query($sql)
    {
        $result = mysql_query($sql);
        if (!$result) {
            throw new \RuntimeException(mysql_error());
        }

        if ($result === true) {
            return true;
        }
        $rows = array();
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Connect to database
     */
    protected function connect($options)
    {
        $connection = mysql_connect(
            $options['host'].':'.$options['port'],
            $options['username'],
            $options['password']
        );

        if (!$connection) {
            throw new \RuntimeException(mysql_error());
        }

        mysql_select_db($options['dbname']);
    }
}