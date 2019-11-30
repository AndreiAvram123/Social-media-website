<?php


class User
{
   private $userId;
   private $username;
   private $email;
   private $joinDate;


    public function __construct($db_row)
    {
        $this->userId = $db_row['user_id'];
        $this->username = $db_row['username'];
        $this->email = $db_row['email'];
        $this->joinDate =$db_row['creation_date'];
    }


    public function getUserId()
    {
        return $this->userId;
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


    public function getEmail()
    {
        return $this->email;
    }


}