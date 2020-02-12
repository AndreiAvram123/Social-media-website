<?php

require_once "Data/Database.php";

class UserAccountManager
{
    private static $userAccountManager;
    protected $_dbHandler;
    protected $_dbInstance;

    //method used to create a singleton pattern
    public static function getInstance()
    {
        if (self::$userAccountManager === null) {
            self::$userAccountManager = new self();
        }
        return self::$userAccountManager;

    }

    private function __construct()
    {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandler = $this->_dbInstance->getDatabaseConnection();
    }


    public function sendEmailVerification($email)
    {
        $msg = "Please click on the link below to verify your email \n";
        $msg .= "http://sgb967.poseidon.salford.ac.uk/cms/RestfulServices.php?verifyEmail=";
        $msg .= md5($email);
        mail($email, "Verify your email", $msg);
    }

    public function sendResetPasswordEmail($email)
    {
        $msg = "Please click on the link below to change your password \n";
        $msg .= "http://sgb967.poseidon.salford.ac.uk/cms/RestfulServices.php?resetPasswordURL=";
        $msg .= md5($email);
        mail($email, "Change your password", $msg);
    }

    public function changeUserPassword($userEmailEncrypted, $newPassword)
    {
        $userEmail = $this->getUserEmailFromEncryptedEmail($userEmailEncrypted);
        $encryptedNewPassword = md5($newPassword);
        $query = "UPDATE users SET password = '$encryptedNewPassword' WHERE email = '$userEmail'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }

    private function getUserEmailFromEncryptedEmail($encryptedEmail)
    {
        $query = "SELECT email FROM users";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        while ($row = $result->fetch()) {
            if ($encryptedEmail === md5($row["email"])) {
                return $row["email"];
            }
        }
    }

    public function markEmailVerified($encryptedEmail)
    {
        $userEmail = $this->getUserEmailFromEncryptedEmail($encryptedEmail);
        $query = "UPDATE users SET email_verified = TRUE WHERE email = '$userEmail'";
        $this->executeQuery($query);
    }

    private function executeQuery($query)
    {
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }

}