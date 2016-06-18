<?php

namespace Foodora\DB;

/**
 * Database object interface
 *
 * @author K. Christofilos <kostas.christofilos@gmail.com>
 */
interface DBInterface
{
    /**
     * Executes a query and returns an array or boolean
     * 
     * @param string $sql
     * 
     * @return mixed
     */
    public function query($sql);
}