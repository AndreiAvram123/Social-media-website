<?php
session_start();
require_once "SessionHandler.php";
require_once "Data/DataManager.php";
$view = new stdClass();
$view->pageTitle = "Home";

$dbHandle = new DataManager();
$view -> posts = $dbHandle->fetchMostRecentPosts();

if (isset($_POST['signOutButton'])) {
    signUserOut();
    $view->redirectHome = true;

}
$view ->isUserLoggedIn = isset($_SESSION['user_id']);

require_once "Views/index.phtml";


?>


