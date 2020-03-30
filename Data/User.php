<?php

/**
 * Class used to create objects that contain
 * the data from a user row in the database
 */
include_once "utilities/Functions.php";

class User implements JsonSerializable
{
    private $userId;
    private $username;
    private $email;
    private $joinDate;
    private $profilePicture;
    private $isEmailVerified;
    private $lastMessage;

    public function __construct($db_row)
    {
        $this->userId = $db_row['user_id'];
        $this->username = $db_row['username'];
        $this->email = $db_row['email'];
        $this->joinDate = $db_row['creation_date'];
        $this->isEmailVerified = $db_row['email_verified'];

    }


    public function jsonSerialize()
    {
        return
            [
                'userId' => $this->userId,
                'username' => $this->username,
            ];
    }


    public function setLastMessage($lastMessage)
    {
        $this->lastMessage = $lastMessage;
    }


    public function getLastMessage()
    {
        return $this->lastMessage;
    }


    public function isEmailVerified()
    {
        return $this->isEmailVerified;
    }


    public function getUserId()
    {
        return $this->userId;
    }

    public function getProfilePicture()
    {
        return $this->profilePicture;
    }


    public function getUsername()
    {
        return $this->username;
    }


    public function getJoinDate()
    {
        return $this->joinDate;
    }


    public function getEmail()
    {
        return $this->email;
    }


}