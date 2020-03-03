<?php
require_once "Data/DataManager.php";
require_once "Data/Validator.php";

/**
 * This class in used to handle actions
 * regarding to logging in or registering users
 */
class SessionManager
{
    private $_dbManager;
    //create singleton pattern for this class
    private static $sessionHandler;
    private $_validator;

    //method used to create the singleton pattern
    public static function getInstance()
    {
        if (self::$sessionHandler !== null) {
            return self::$sessionHandler;
        } else {
            self::$sessionHandler = new SessionManager();
            return self::$sessionHandler;
        }
    }

    private function __construct()
    {
        $this->_dbManager = DataManager::getInstance();
        $this->_validator = new Validator();
    }

    public function signUserOut()
    {
        unset($_SESSION['user_id']);

    }

    private function addUserDataToSession($userId)
    {
        $_SESSION['user_id'] = $userId;
    }

    /**
     * This method is used in order to login a user
     * Returns true if the login action has
     * been successful or an error message if not
     * @param $email
     * @param $enteredPassword
     * @return bool|string
     */
    public function loginUser($email, $enteredPassword)
    {
        $databaseHandler = DataManager::getInstance();
        $check = $this->_validator->areLoginCredentialsValid($email, $enteredPassword);
        if ($check !== true) return $check;
        $userPasswordDB = $databaseHandler->getUserPasswordFromDB($email);
        if (empty($userPasswordDB)) return "Hmm...Seems like your account does not exist";
        if (md5($enteredPassword) != $userPasswordDB) return "Incorrect password";
        $user = $databaseHandler->getUserFromEmail($email);
        if ($user->isEmailVerified() == false) return "Email not verified";
        $this->addUserDataToSession($user->getUserId());

        return true;


    }


    /**
     * Method used in order to create a new user
     * It checks the details entered and return true
     * if it managed to create a new user or
     * an error message if not
     * @param $username
     * @param $email
     * @param $password
     * @param $image
     * @param $creationDate
     * @return bool|string
     */
    public function createUser($username, $email, $password, $image, $creationDate)
    {
        $databaseHandler = DataManager::getInstance();
        $check = $this->checkRegisterCredentials($username, $email, $password, $image);
        if ($check === true) {
            $imageLocation = null;
            if (!empty($image)) {
                $imageLocation = $databaseHandler->uploadImageToServer($image, $_FILES["profilePicture"]["tmp_name"], "images/users/");
                $imageLocation = $_SERVER['DOCUMENT_ROOT'] . $imageLocation;
            }
            //the user will have a default image is he does not choose one
            $databaseHandler->createUser($username, $email, $password, $creationDate, $imageLocation);
            return true;
        } else {
            return $check;
        }
    }

    private function checkRegisterCredentials($username, $email, $password, $image)
    {
        if (empty($username)) {
            return "You have not entered an email";
        }
        if (strlen($email) < 5) {
            return "Your email is not long enough";
        }
        if (empty($email)) {
            return "You have not entered an email";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Your email is not valid";
        }
        if (empty($password)) {
            return "You have not entered a password";
        }
        if ($email)
            if (strlen($password) < 7) {
                return "Your password is not strong enough";
            }
        if ($this->_dbManager->usernameExists($username)) {
            return "The username already exists";
        }
        if ($this->_dbManager->emailExists($email)) {
            return "Email already used";
        }
        if (!empty($image)) {
            return $this->_validator->isProfileImageValid($image);
        }
        return true;

    }


}

?>