<?php
require_once("Data/Database.php");
require_once("Data/Message.php");

class ChatDatabase
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

    public function getAllMessagesWithUser($user1Id, $user2Id)
    {
        $query = "SELECT * FROM messages WHERE (sender_id = '$user1Id'
       AND receiver_id = '$user2Id') OR (sender_id = '$user2Id' AND receiver_id='$user1Id')";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $messages = [];
        while ($row = $result->fetch()) {
            $messages[] = new Message($row);
        }
        return $messages;
    }

    public function insertNewMessage($messageContent, $date, $sender_id, $receiver_id)
    {
        $query = "INSERT INTO messages VALUES (NULL,'$messageContent','$date',
                             '$sender_id','$receiver_id')";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }

    public function getNewMessages($lastMessageDate, $user1Id, $user2Id)
    {
        $query = "SELECT * FROM messages WHERE '$lastMessageDate' < message_date AND  
                             ((receiver_id = '$user1Id' AND sender_id = '$user2Id')
                              OR (receiver_id ='$user2Id' AND sender_id = '$user1Id'))";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $messages = [];
        while ($row = $result->fetch()) {
            $messages[] = new Message($row);
        }
        return $messages;
    }

}