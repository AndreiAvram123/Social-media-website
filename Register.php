<?php

session_start();
require_once "Data/SessionManager.php";
require_once "Management/UserAccountManager.php";
$view = new stdClass();
$view->pageTitle = "Register";
$view->isUserLoggedIn = isset($_SESSION['user_id']);

if (isset($_POST['registerButton'])) {
    $username = $_POST['usernameInput'];
    $email = $_POST['emailInput'];
    $password = $_POST['passwordInput'];
    $reenteredPassword = $_POST['confirmedPasswordInput'];
    $creationDate = date('Y-m-d H:i:s');
    //the user may not want to include a profile picture
    //but to have a default one
    $image = $_FILES["profilePicture"]["name"];
    $result = SessionManager::getInstance()->createUser($username, $email, $password, $image, $creationDate);
    UserAccountManager::getInstance()->sendEmailVerification($email);
    if ($result === true) {
        $view->warningMessage = "You successfully created your account ! Go to login page now.";
    } else {
        $view->errorMessage = $result;

    }
}
include "Views/Register.phtml";
?>