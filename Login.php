<?php
session_start();
include_once "Data/SessionManager.php";
include_once "Management/UserAccountManager.php";

$view = new stdClass();
$view->pageTitle = "Login";
$sessionManager = SessionManager::getInstance();

if (isset($_POST['resetPasswordButton'])) {
    UserAccountManager::getInstance()->sendResetPasswordEmail($_POST['emailResetPassword']);
}
if (isset($_POST['resendVerificationEmail'])) {
    UserAccountManager::getInstance()->sendEmailVerification($_POST['userEmailResendVerification']);
    $view->warningMessage = "We have send you another verification email. Please check your inbox";
    
}

if (isset($_POST['loginButton'])) {
    $email = htmlentities($_POST['emailSignIn']);
    $enteredPassword = htmlentities($_POST['passwordSignIn']);
    $loginResult = $sessionManager->loginUser($email, $enteredPassword);
    if ($loginResult !== true) {
        if ($loginResult === "Email not verified") {
            $view->emailNotVerified = true;
            $view->userEmail = $email;
        }
        $view->errorMessage = $loginResult;

    }
}
include "Views/Login.phtml";

