<?php

namespace Config;

use Config\Constants;

/**
 * Base model
 *
 * PHP version 5.4
 */
abstract class DB_Connect
{ 

    /**
     * Get the database connection
     *
     * @return mixed
     */
    protected static function getDB()
    {
        static $db = null;

        if ($db === null) {
            $server=  Constants::DB_HOST;
            $username= Constants::DB_USER;
            $password= Constants::DB_PASSWORD;
            $dbname=  Constants::DB_NAME;

            $db= mysqli_connect($server, $username, $password, $dbname);
            // Check if connection was successful
            if (mysqli_connect_errno()) {
                die("Failed to connect to database: " . mysqli_connect_error());
            }
            $db->set_charset("utf8");

        }

        return $db;
    }
}
