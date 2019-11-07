<?php

class User
{
   private $username;
   private $password;
   private $email;
   private $creationDate;

    /**
     * User constructor.
     * @param $username
     * @param $password
     * @param $email
     * @param $creationDate
     */
    public function __construct($username, $password, $email, $creationDate)
    {
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        $this->creationDate = $creationDate;
    }


    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
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
    public function getCreationDate()
    {
        return $this->creationDate;
    }


}