<?php
session_start();
include "SessionHandler.php";

$view = new stdClass();
$view->pageTitle = "Login";
$view->isUserLoggedIn = false;


if (isset($_POST['loginButton'])) {
   if(loginUser()){
      $view->redirectHome = true;
   }
}
include "Views/Login.phtml";

?>