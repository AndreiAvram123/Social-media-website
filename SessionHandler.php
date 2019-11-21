<?php
require_once "Data/DatabaseHandler.php";
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
    $databaseHandler = new DatabaseHandler();

    if(areLoginCredentialsValid($email, $enteredPassword)){
    $userPasswordDB = $databaseHandler->getUserPasswordFromDB($email);
    if (!empty($userPasswordDB)) {
        if (md5($enteredPassword) == $userPasswordDB) {
            addUserDataToSession($databaseHandler->getUserIDFromEmail($email));
            return true;
        }
    }
return false;
}
}

function areLoginCredentialsValid($email, $password)
{
    if (empty($email)) {
        displayWarningMessage("You have not entered an email");
        return false;
    }
    if (empty($password)) {
        displayWarningMessage("You have not entered a password");
        return false;
    }
    return true;
}

function createUser($username,$email,$password,$image,$creationDate){
    $databaseHandler = DatabaseHandler::getInstance();
    $databaseHandler->uploadFile($image, "images/");
    $databaseHandler->createUser($username,$email,$password,$creationDate);

}