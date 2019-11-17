<?php

require_once "Data/DatabaseHandler.php";

function signUserOut()
{
    unset($_SESSION['user_email']);
    
}

function addUserDataToSession($email)
{
    $_SESSION['user_email'] = $email;
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
            addUserDataToSession($email);
        }
    }else{
        displayWarningMessage("Hmm...It seems like your account does not exist");
    }

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

function createUser(){
    $databaseHandler = DatabaseHandler::getInstance();
    $username = $_POST['usernameInput'];
    $email = $_POST['emailInput'];
    $password = $_POST['passwordInput'];
    $reenteredPassword = $_POST['confirmedPasswordInput'];
    $reenteredPassword = $databaseHandler->uploadFile($_FILES["profilePicture"]["name"], "images/");
    $creationDate = date('Y/m/d');
    //verify details

    $databaseHandler->createUser($username,$email,$password,$creationDate);

}