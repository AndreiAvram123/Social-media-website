<?php

require_once "Data/Database.php";
require_once "Data/FriendRequest.php";
require_once "Data/User.php";
class FriendsDatabase
{

    protected $_dbHandler;
    protected $_dbInstance;
    //create a singleton pattern for this as well
    private static $friendsDatabase;
    //define how may posts should be displayed on page
    private $postPerPage = 10;

    //method used to create a singleton pattern
    public static function getInstance()
    {
        if (self::$friendsDatabase === null) {
            self::$friendsDatabase = new self();
        }
        return self::$friendsDatabase;

    }

    public function __construct()
    {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandler = $this->_dbInstance->getDatabaseConnection();
    }

    public function getAllFriendRequestsForUser($user_id)
    {
        $query = "SELECT * FROM friend_requests WHERE receiver_id = '$user_id'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $friendRequests=[];
        while($row = $result->fetch()){
            $friendRequests[] = new FriendRequest($row);
        }
        return $friendRequests;
    }
    /**
     * Get an array of users(friends) by passing the id of the current
     * logged in user
     * @param $user_id
     * @return array
     */
    public function getAllFriends($user_id)
    {
        $query = "SELECT * FROM users WHERE user_id IN 
        (SELECT user2_id from friends WHERE user1_id = '$user_id') OR
         user_id IN (SELECT user1_id from friends WHERE user2_id = '$user_id')";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $friends = [];
        while ($row = $result->fetch()) {
            $friends[] = new User($row);
        }
        return $friends;
    }

    /**
     * Method used to add an User to another user's friends list
     * @param $currentUserId - the id of the current logged in user
     * @param $userId - the id of the user that should be added to the friends list
     */
    public function addToFriendList($currentUserId, $userId)
    {
        $query = "INSERT INTO friends VALUES ('$currentUserId','$userId')";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }

    /**
     * Method used to remove a friend from the friends' list of
     * the current user
     * @param $user_id
     * @param $friendId
     */
    public function removeFriend($user_id, $friendId)
    {
        $query = "DELETE FROM friends WHERE user1_id ='$user_id' AND user2_id = '$friendId'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();

    }
    public function sendFriendRequest($sender_id, $receiver_id)
    {
        $query = "INSERT INTO friend_requests VALUES (NULL,'$sender_id','$receiver_id')";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }

    public function getAllFriendRequestsIds()
    {
        $query = "SELECT request_id FROM friend_requests";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $ids=[];
        while($row = $result->fetch()){
            $ids[] = $row["request_id"];
        }
        return $ids;
    }

    /**
     * Use this method in order to accept a friend invitation
     * and persist the data in the database
     * @param $senderId - the id of the user who sent the friend request
     * @param $receiverId - the id of the user who received the invitation
     */
    public function acceptFriendRequest($senderId,$receiverId)
    {
        $this->addToFriendList($receiverId,$senderId);
        $this->deleteFriendRequest($senderId,$receiverId);
    }

    /**
     * Use this function to delete a friend request by the sender and
     * receiver id
     * @param $senderId
     * @param $receiverId
     */
    private function deleteFriendRequest($senderId, $receiverId)
    {
        $query = "DELETE FROM friend_requests WHERE sender_id = '$senderId' AND receiver_id = '$receiverId'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }
    /**
     * Use this method in order to reject a friend invitation
     * and persist the data in the database
     * @param $senderId - the id of the user who sent the friend request
     * @param $receiverId - the id of the user who received the invitation
     */
    public function rejectFriendRequest($senderId, $receiverId)
    {
        $this->deleteFriendRequest($senderId,$receiverId);
    }

}