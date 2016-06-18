<?php

namespace Foodora\DB\Mysql;
use Foodora\DB\DBInterface;

/**
 * Database driver for PDO
 *
 * @author K. Christofilos <kostas.christofilos@gmail.com>
 * @link http://php.net/manual/en/book.pdo.php
 */
class PDO implements DBInterface
{
    /** @var \PDO */
    protected $db;

    public function __construct(array $options)
    {
        $dsnParams = array();
        foreach($options as $key => $value) {
            if ($key !== 'username' && $key !== 'password') {
                $dsnParams[] = "$key=$value";
            }
        }
        $dsn = 'mysql:'.implode(';', $dsnParams);

        $this->db = new \PDO($dsn, $options['username'], $options['password']);
    }

    public function query($sql)
    {
        $result = $this->db->query($sql, \PDO::FETCH_ASSOC);
        if (!$result) {
            $error = $this->db->errorInfo();
            throw new \RuntimeException($error[2]);
        }
        if ($result === true) {
            return $result;
        }
        $rows = array();
        foreach($result as $row) {
            $rows[] = $row;
        }

        return $rows;
    }
}