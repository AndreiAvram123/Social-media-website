<?php
session_start();
require_once "SessionHandler.php";
require_once  "Data/DatabaseHandler.php";
$view = new stdClass();
$view->pageTitle = "Home";
$dataset = new DatabaseHandler();
$view -> posts = $dataset->fetchMostRecentPosts();

if (isset($_POST['signOutButton'])) {
    signUserOut();
    $view->redirectHome = true;

}
$view ->isUserLoggedIn = isset($_SESSION['user_id']);

require_once "Views/index.phtml";


if (isset($_POST['registerUser'])) {
    createUser();
}

?>


