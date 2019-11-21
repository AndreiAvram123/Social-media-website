<?php
session_start();
include "SessionHandler.php";

$view = new stdClass();
$view->pageTitle = "Login";
$view->isUserLoggedIn = false;
include "Views/Login.phtml";

if (isset($_POST['loginButton'])) {
   if(loginUser()){
      $view->redirectHome = true;
   }
}
