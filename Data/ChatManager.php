<?php
require_once "Message.php";
require_once "Database.php";
class ChatManager
{
    private static $chatManager;
    private $_dbInstance;
    private $_dbHandler;
    //method used to create a singleton pattern
    public static function getInstance()
    {
        if (self::$chatManager !== null) {
            return self::$chatManager;
        } else {
            self::$chatManager = new self();
            return self::$chatManager;
        }

    }

    public function __construct()
    {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandler = $this->_dbInstance->getDatabaseConnection();
    }

    public function postMessage($messageContent, $messageDate)
    {
        $query = "INSERT INTO messages VALUES (NULL,'$messageContent','$messageDate')";
        echo $query;
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }
}