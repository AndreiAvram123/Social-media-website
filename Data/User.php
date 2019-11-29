<?php


class User
{
   private $userId;
   private $username;
   private $profilePicture;
   private $joinDate;


    public function __construct($db_row)
    {
        $this->userId = $db_row['user_id'];
        $this->username = $db_row['username'];
        $this->profilePicture = $db_row['email'];
        $this->joinDate =$db_row['creation_date'];
    }




    public function getUsername()
    {
        return $this->username;
    }


    public function getProfilePicture()
    {
        return $this->profilePicture;
    }


    public function getJoinDate()
    {
        return $this->joinDate;
    }

}