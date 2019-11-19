<?php
session_start();
include "SessionHandler.php";

$view = new stdClass();
$view->pageTitle = "Login";
$view->isUserLoggedIn = isset($_SESSION['user_email']);


if (isset($_POST['loginButton'])) {
   if(loginUser()){
      $view->userJustLoggedIn = true;
   }
}
include "Views/Login.phtml";

?>