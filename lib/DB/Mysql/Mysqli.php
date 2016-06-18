<?php

namespace Foodora\DB\Mysql;
use Foodora\DB\DBInterface;

/**
 * Database driver for Mysqli
 *
 * @author K. Christofilos <kostas.christofilos@gmail.com>
 * @link http://php.net/manual/en/book.mysqli.php
 */
class Mysqli implements DBInterface
{
    /** @var \mysqli */
    protected $db;

    public function __construct(array $options)
    {
        $this->db = new \mysqli(
            $options['host'], 
            $options['username'], 
            $options['password'], 
            $options['dbname'], 
            $options['port']
        );

        if ($this->db->connect_error) {
            throw new \RuntimeException($this->db->connect_error);
        }
    }
    
    public function query($sql)
    {
        $result = $this->db->query($sql);
        if (!$result) {
            throw new \RuntimeException($this->db->error);
        }

        if ($result === true) {
            return true;
        }
        $rows = array();
        while($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;


    }
}