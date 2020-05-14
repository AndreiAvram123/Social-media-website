<?php

class NullSafeUser extends User
{
    public function __construct()
    {
        $dataObject = [];
        $dataObject['user_id'] = -1;
        $dataObject['username'] = "Unknown";
        $dataObject['email'] = "Unknown";
        $dataObject['creation_date'] = "Unknown";
        $dataObject['email_verified'] = true;
        parent::__construct($dataObject);
    }
}