<?php
session_start();
include "SessionHandler.php";

$view = new stdClass();
$view->pageTitle = "Login";
$view->isUserLoggedIn = isset($_SESSION['user_id']);

if (isset($_POST['loginButton'])) {
    $email = htmlentities($_POST['emailSignIn']);
    $enteredPassword = htmlentities($_POST['passwordSignIn']);
    $loginResult = loginUser($email,$enteredPassword);
    if($loginResult === true){
        $view->redirectHome = true;
    }else{
        //show user and error message
        $view -> errorMessage = $loginResult;
    }
}
include "Views/Login.phtml";

