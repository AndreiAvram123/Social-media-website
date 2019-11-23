<?php
session_start();
require_once "Data/DataManager.php";
$view = new stdClass();
$view->pageTitle = "My posts";
$view->isUserLoggedIn = isset($_SESSION['user_id']);
$dbHandler = new DataManager();
$view->displayRemoveButton = true;

$postIDs= $dbHandler->getAllPostsIDs();

foreach ($postIDs as $postID) {
    if (isset($_POST["Attempt". $postID])) {
        $view -> postIdToRemove = $postID;
    }
}
foreach ($postIDs as $postID) {
    if (isset($_POST["Remove". $postID])) {
        $dbHandler->removePost($postID);
    }
}

//get the posts after the user possibly pressed remove
$view->myPosts = $dbHandler->getAllUserPosts($_SESSION['user_id']);

include "Views/MyPosts.phtml";
?>