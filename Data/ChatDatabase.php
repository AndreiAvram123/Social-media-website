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

    /**
     * This method is used to upload an image to the server by
     * giving the following parameters
     * The method encrypts the image name as as security reason
     * @param $target_file - the location of the file on the user's computer
     * @param $tempName - the temporary name of the image
     * @param $target_dir - where the image should be place in the server
     * @return string - the image location on the server in order
     * to be stored in a database table
     */
    public function uploadImageToServer($target_file, $tempName, $target_dir)
    {
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        //once you encrypt the image, the algorithm will also encrypt
        //the file extension. That's why I need to add it as well
        $targetLocation = $target_dir . md5($target_file) . '.' . $imageFileType;
        move_uploaded_file($tempName, $targetLocation);
        return $targetLocation;
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
        $query = "INSERT INTO messages VALUES (NULL,'$messageContent','$date', '$sender_id','$receiver_id',NULL)";

        $this->executeQuery($query);

    }

    public function insertImageMessage($imagePath, $date, $sender_id, $receiver_id)
    {
        $query = "INSERT INTO messages VALUES (NULL,NULL,'$date', '$sender_id','$receiver_id','$imagePath')";
        $this->executeQuery($query);
    }

    public function getNewMessages($lastMessageId, $user1Id, $user2Id)
    {
        $query = "SELECT * FROM messages WHERE '$lastMessageId' < message_id AND  
                             ((receiver_id = '$user1Id' AND sender_id = '$user2Id')
                              OR (receiver_id ='$user2Id' AND sender_id = '$user1Id'))";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        return $this->getProcessedMessages($result);
    }

    private function getProcessedMessages($result)
    {
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
        if ($row == false) {
            return null;
        } else {
            return $row['chat_id'];
        }

    }

    public function setUserIsTyping($chatId, $userId, $isTyping)
    {
        $query = "UPDATE chat_live_functions SET user_is_typing = $isTyping  WHERE chat_id='$chatId' AND user_id = '$userId'";
        $this->executeQuery($query);
    }

    public function checkUserIsTyping($chatId, $currentUserId)
    {
        $query = "SELECT user_is_typing FROM chat_live_functions WHERE chat_id = '$chatId' AND user_id != '$currentUserId'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        if ($row["user_is_typing"] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function fetchOldMessages($user1Id, $user2Id, $offset)
    {
        $query = "SELECT * FROM messages WHERE  
                             ((receiver_id = '$user1Id' AND sender_id = '$user2Id')
                              OR (receiver_id ='$user2Id' AND sender_id = '$user1Id'))
                              ORDER BY message_id DESC LIMIT 20 OFFSET $offset ";

        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        return $this->getProcessedMessages($result);

    }

    public function fetchLastMessage($user1Id, $user2Id)
    {
        $query = "SELECT  * FROM messages WHERE
        ((receiver_id = '$user1Id' AND sender_id = '$user2Id')
                              OR (receiver_id ='$user2Id' AND sender_id = '$user1Id'))
ORDER BY message_id DESC LIMIT 1";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        return new Message($row);
    }


}