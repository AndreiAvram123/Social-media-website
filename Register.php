<?php
require_once "Data/SessionManager.php";
$view = new stdClass();
$view->pageTitle = "Register";
$view ->isUserLoggedIn =isset($_SESSION['user_id']);


include "Views/Register.phtml";

if (isset($_POST['registerButton'])) {
    $username = $_POST['usernameInput'];
    $email = $_POST['emailInput'];
    $password = $_POST['passwordInput'];
    $reenteredPassword = $_POST['confirmedPasswordInput'];
    $creationDate = date('Y/m/d');
    //the user may not want to include a profile picture
    //but to have a default one
    if(isset($_FILES["profilePicture"]["name"])){
        $image = $_FILES["profilePicture"]["name"];
    }else{
        $image = null;
    }
    $result = SessionManager::getInstance()->createUser($username, $email, $password, $image,$creationDate);
    if($result!== true){
        $view->errorMessage = $result;
    }else{
        $view->showSuccessAlert = true;
    }
}

?>