<?php

class UserModelAsync
{
    public $userId;
    public $username;
    public $profilePicture;

    public function __construct($db_row)
    {
        $this->userId = md5($db_row['user_id']);
        $this->username = $db_row['username'];
        $this->profilePicture = $db_row['profile_picture'];
    }
}