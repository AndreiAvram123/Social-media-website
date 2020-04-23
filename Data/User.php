<?php

/**
 * Class used to create objects that contain
 * the data from a user row in the database
 */
include_once "utilities/Functions.php";

class User implements JsonSerializable
{
    protected $userId;
    protected $username;
    protected $email;
    protected $profilePicture;
    private $joinDate;
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

                'userID' => $this->getUserId(),
                'username' => $this->getUsername(),
                'lastMessage' => $this->getLastMessage(),
                'email' => $this->email,
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


    public function getUserId()
    {
        return $this->userId;
    }

    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getProfilePicture()
    {
        return $this->profilePicture;
    }

    /**
     * @return mixed
     */
    public function getJoinDate()
    {
        return $this->joinDate;
    }

    /**
     * @return mixed
     */
    public function IsEmailVerified()
    {
        return $this->isEmailVerified;
    }

}