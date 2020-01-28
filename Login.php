<?php
session_start();
include_once "Data/SessionManager.php";

$view = new stdClass();
$view->pageTitle = "Login";
$sessionManager = SessionManager::getInstance();

if (isset($_POST['loginButton'])) {
    $email = htmlentities($_POST['emailSignIn']);
    $enteredPassword = htmlentities($_POST['passwordSignIn']);
    $captchaValue = htmlentities($_POST['captchaValueEntered']);
    if ($captchaValue !== $_SESSION['captcha_code']) {
        $view->errorMessage = "Invalid code, please try again";
    } else {
        $loginResult = $sessionManager->loginUser($email, $enteredPassword);
        if ($loginResult !== true) {
            //show user and error message
            $view->errorMessage = $loginResult;
        }
    }
}
include "Views/Login.phtml";

