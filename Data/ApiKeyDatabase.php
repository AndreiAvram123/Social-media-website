<?php
require_once ("Data/Database.php");
class ApiKeyDatabase
{
    //create singleton
    private static $instance;
    private $_dbInstance;
    private $_dbHandler;

    public static function getInstance()
    {

        if (self::$instance == null) {
            self::$instance = new ApiKeyDatabase();
        }
        return self::$instance;
    }
    private function __construct()
    {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandler = $this->_dbInstance->getDatabaseConnection();
    }

    public function fetchApiKeyForIPAddress($ip)
    {

    }
}