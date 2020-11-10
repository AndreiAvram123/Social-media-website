<?php

class Database
{
    protected static $_dbInstance = null;
    protected $_dbHandle;

    public static function getInstance()
    {
        //login details
        if (self::$_dbInstance === null) {
            $username = 'sgb967';
            $host = 'sgb967.poseidon.salford.ac.uk:3306';
            $password = 'Wanrltwaezakmi1239';
            $dbName = 'sgb967';
            // check if there is an instance
            self::$_dbInstance = new self($username, $password, $host, $dbName);
        }
        return self::$_dbInstance;
    }

    private function __construct($username, $password, $host, $database)
    {
        try {
            $this->_dbHandle = new PDO("mysql:host=$host;dbname=$database", $username, $password); // creates the database handle with connection info
        } catch (PDOException $e) { // catch any failure to connect to the database
            echo $e->getMessage();
        }
    }

    public function getDatabaseConnection()
    {
        return $this->_dbHandle; // returns the PDO handle to be used                                        elsewhere
    }


}
