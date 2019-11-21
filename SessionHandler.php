<?php
require_once "Data/DataManager.php";

function signUserOut()
{
    unset($_SESSION['user_id']);

}

function addUserDataToSession($userId)
{
    $_SESSION['user_id'] = $userId;
}

function loginUser()
{
    $email = $_POST['emailSignIn'];
    $enteredPassword = $_POST['passwordSignIn'];
    $databaseHandler = new DataManager();
    $check = areLoginCredentialsValid($email, $enteredPassword);
    if ($check === true) {
        $userPasswordDB = $databaseHandler->getUserPasswordFromDB($email);
        if (!empty($userPasswordDB)) {
            if (md5($enteredPassword) == $userPasswordDB) {
                addUserDataToSession($databaseHandler->getUserIDFromEmail($email));
                return true;
            } else {
                return "Incorrect password";
            }
        } else {
            return "Hmm...Seems like your account does not exist";
        }
    }else{
        return $check;
    }
}

function areLoginCredentialsValid($email, $password)
{
    if (empty($email)) {
        return "You have not entered an email";
    }
    if (empty($password)) {
        return "You have not entered a password";
    }
    return true;
}

function createUser($username, $email, $password, $image, $creationDate)
{
    $databaseHandler = DataManager::getInstance();
    $check = checkRegisterCredentials($username,$email,$password);
    if($check === true) {
         //todo
        //should add the option for the user to upload a profile picture
        //$imageLocation = $databaseHandler->uploadFile($image, "images/");
        $databaseHandler->createUser($username, $email, $password, $creationDate);
        return true;
    }else {
        return $check;
    }
}
function checkRegisterCredentials($username,$email,$password){
    if (empty($username)) {
        return "You have not entered an email";
    }
    if (empty($email)) {
        return "You have not entered an email";
    }
    if (empty($password)) {
        return "You have not entered a password";
    }
    if(DataManager::getInstance()->usernameExists($username)){
       return "The username already exists";
    }
    return true;

}