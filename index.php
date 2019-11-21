<?php
session_start();
require_once "SessionHandler.php";
require_once  "Data/DatabaseHandler.php";
$view = new stdClass();
$view->pageTitle = "Home";

$dbHandle = new DatabaseHandler();
$view -> posts = $dbHandle->fetchMostRecentPosts();
$view ->displayRemoveButton = false;

if (isset($_POST['signOutButton'])) {
    signUserOut();
    $view->redirectHome = true;

}
$view ->isUserLoggedIn = isset($_SESSION['user_id']);

require_once "Views/index.phtml";


?>


