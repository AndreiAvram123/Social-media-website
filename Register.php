<?php
require_once "SessionHandler.php";

$view = new stdClass();
$view->pageTitle = "Register";
$view ->isUserLoggedIn =false;

include "Views/Register.phtml";

if (isset($_POST['registerButton'])) {
    $username = $_POST['usernameInput'];
    $email = $_POST['emailInput'];
    $password = $_POST['passwordInput'];
    $reenteredPassword = $_POST['confirmedPasswordInput'];
    $creationDate = date('Y/m/d');
    if(isset($_FILES["profilePicture"]["name"])){
        $image = $_FILES["profilePicture"]["name"];
    }else{
        $image = null;
    }
    $result =  createUser($username, $email, $password, $image,$creationDate);
    if($result!== true){
        $view->errorMessage = $result;
    }
}
?>