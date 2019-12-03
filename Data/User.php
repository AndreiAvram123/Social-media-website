<?php


class User
{
   private $userId;
   private $username;
   private $email;
   private $joinDate;
   private $profilePicture;

    public function __construct($db_row)
    {
        $this->userId = $db_row['user_id'];
        $this->username = $db_row['username'];
        $this->email = $db_row['email'];
        $this->joinDate =$db_row['creation_date'];
        $this->profilePicture = $db_row['profile_picture'];
    }


    public function getUserId()
    {
        return $this->userId;
    }

    public function getProfilePicture(){
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