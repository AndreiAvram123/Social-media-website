<?php
session_start();
require_once "SessionHandler.php";
require_once "Data/Dataset.php";

$view = new stdClass();
$view->pageTitle = "Home";
$dataset = new Dataset();
$view -> posts = $dataset->getMostRecentPosts();

if (isset($_POST['signOutButton'])) {
    signUserOut();

}
$view ->isUserLoggedIn = isset($_SESSION['user_email']);

require_once "Views/index.phtml";


if (isset($_POST['registerUser'])) {
    createUser();
}

?>


