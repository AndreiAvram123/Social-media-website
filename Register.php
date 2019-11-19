<?php
require_once "SessionHandler.php";

$view = new stdClass();
$view->pageTitle = "Register";

include "Views/Register.phtml";

if (isset($_POST['registerButton'])) {
    $username = $_POST['usernameInput'];
    $email = $_POST['emailInput'];
    $password = $_POST['passwordInput'];
    $reenteredPassword = $_POST['confirmedPasswordInput'];
    $creationDate = date('Y/m/d');
    $image = $_FILES["profilePicture"]["name"];
    $creationDate = date('Y/m/d');
    createUser($username, $email, $password, $image,$creationDate);
}
?>