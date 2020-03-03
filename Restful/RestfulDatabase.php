<?php

require_once ("Data/Database.php");
require_once ("Data/Post.php");

class RestfulDatabase
{
    protected $_dbHandler;
    protected $_dbInstance;
    //create a singleton pattern for this as well
    private static $chatDatabase;

    //method used to create a singleton pattern
    public static function getInstance()
    {
        if (self::$chatDatabase === null) {
            self::$chatDatabase = new self();
        }
        return self::$chatDatabase;

    }

    public function __construct()
    {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandler = $this->_dbInstance->getDatabaseConnection();
    }

    private function executeQuery($query){
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }

}