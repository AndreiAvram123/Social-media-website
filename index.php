<?php
session_start();
require_once "Data/SessionManager.php";
require_once "Data/DataManager.php";
require_once "Data/ChatManager.php";
require_once "Data/FriendsDatabase.php";
require_once("Api/ApiKeyManager.php");

$view = new stdClass();
$view->pageTitle = "Home";
$dbHandle = DataManager::getInstance();
$dbFriends = FriendsDatabase::getInstance();

$numberOfPages = $dbHandle->getNumberOfPages();
$view->numberOfPages = $numberOfPages;
$view->categories = $dbHandle->getAllCategories();

if (isset($_POST['signOutButton'])) {
    SessionManager::getInstance()->signUserOut();
    $view->redirectHome = true;
}
//set a default value
$currentPage = 1;

//handle pagination
if (isset($_GET['previousPage'])) {
    for ($i = 1; $i <= $numberOfPages; $i++) {
        if (md5($i) == $_GET['currentPageId']) {
            $currentPage = $i - 1;
        }
    }
}
if (isset($_GET['nextPage'])) {
    for ($i = 1; $i <= $numberOfPages; $i++) {
        if (md5($i) == $_GET['currentPageId']) {
            $currentPage = $i + 1;
        }
    }
}

$view->currentPage = $currentPage;
$view->posts = $dbHandle->getPosts($view->currentPage);
if (isset($_SESSION['user_id'])) {
    $view->friends = $dbFriends->getAllFriends($_SESSION['user_id']);
}

//get the api key to access async functions if needed
$view->isUserLoggedIn = isset($_SESSION['user_id']);
$view->apiKey = ApiKeyManager::getInstance()->obtainApiKey($_SERVER['REMOTE_ADDR']);
require_once "Views/index.phtml";
?>


