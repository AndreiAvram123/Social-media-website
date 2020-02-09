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
        $query = "INSERT INTO messages VALUES (NULL,'$messageContent','$date', '$sender_id','$receiver_id')";

        $result = $this->_dbHandler->prepare($query);
        $result->execute();

    }

    public function getNewMessages($lastMessageId, $user1Id, $user2Id)
    {
        $query = "SELECT * FROM messages WHERE '$lastMessageId' < message_id AND  
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

    public function createChat($user1Id, $user2Id)
    {
        $query = "INSERT INTO chats values (NULL,$user1Id,$user2Id)";
        $this->executeQuery($query);
    }

    public function createChatLiveFunctions($charId, $user1Id, $user2Id)
    {
        $query = "INSERT INTO chat_live_functions VALUES ('$charId','$user1Id',false)";
        $secondQuery = "INSERT INTO chat_live_functions VALUES ('$charId','$user2Id',false)";
        $this->executeQuery($query);
        $this->executeQuery($secondQuery);
    }

    private function executeQuery($query)
    {
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }

    public function fetchChatId($user1Id, $user2Id)
    {
        $query = "SELECT chat_id from chats WHERE (user1_id = '$user1Id' AND user2_id = '$user2Id')
OR (user1_id = '$user2Id' AND user2_id='$user1Id')";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        return $row['chat_id'];
    }

    public function setUserIsTyping($chatId, $userId, $isTyping)
    {
        $query = "UPDATE chat_live_functions SET user_is_typing = $isTyping  WHERE chat_id='$chatId' AND user_id = '$userId'";
        $this->executeQuery($query);
    }

    public function checkUserIsTyping($chatId,$currentUserId)
    {
      $query = "SELECT user_is_typing FROM chat_live_functions WHERE chat_id = '$chatId' AND user_id != '$currentUserId'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        return ($result->fetch())["user_is_typing"];
    }


}