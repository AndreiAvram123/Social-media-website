<?php
session_start();
require ("SessionHandler.php");
require ("Views/index.phtml");

if (isset($_POST['signOutButton'])) {
    signUserOut();
}
if (isset($_POST['loginButton'])) {
    loginUser();
}

if (isset($_POST['registerUser'])) {
    createUser();
}

?>


