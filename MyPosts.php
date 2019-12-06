<?php
session_start();
require_once "Data/DataManager.php";
$view = new stdClass();
$view->pageTitle = "My posts";
$view->isUserLoggedIn = isset($_SESSION['user_id']);

$dbHandler = DataManager::getInstance();
$view->displayRemoveButton = true;


$postIDs= $dbHandler->getAllPostsIDs();
if(isset($_POST['removeButton'])){
    $encryptedPostId = $_POST['removeValue'];
    foreach($postIDs as $postId){
        if($encryptedPostId === md5($postId)){
            $dbHandler ->removePost($postId);
        }
    }
}

//get the posts after the user possibly pressed remove
$view->myPosts = $dbHandler->getAllUserPosts($_SESSION['user_id']);

include "Views/MyPosts.phtml";
?>