<?php
/**
 * This file is the controller for the register action
 */
session_start();
require_once "Data/SessionManager.php";
$view = new stdClass();
$view->pageTitle = "Register";
$view->isUserLoggedIn = isset($_SESSION['user_id']);

if (isset($_POST['registerButton'])) {
    $captchaValue = htmlentities($_POST['captchaValueEntered']);
    if($captchaValue !== $_SESSION['captcha_code'])
    {$view->errorMessage = "Invalid code, please try again";
    }else {
        $username = $_POST['usernameInput'];
        $email = $_POST['emailInput'];
        $password = $_POST['passwordInput'];
        $reenteredPassword = $_POST['confirmedPasswordInput'];
        $creationDate = date('Y-m-d H:i:s');
        //the user may not want to include a profile picture
        //but to have a default one
        $image = $_FILES["profilePicture"]["name"];
        $result = SessionManager::getInstance()->createUser($username, $email, $password, $image, $creationDate);
        if ($result === true) {
            $view->warningMessage = "You successfully created your account ! Go to login page now.";
        } else {
            $view->errorMessage = $result;
        }
    }
}
include "Views/Register.phtml";
?>