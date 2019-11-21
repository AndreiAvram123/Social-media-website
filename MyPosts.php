<?php
session_start();
require_once "Data/DataManager.php";
$view = new stdClass();
$view->pageTitle = "My posts";
$view->isUserLoggedIn = isset($_SESSION['user_id']);
$dbHandler = new DataManager();
$view ->displayRemoveButton = true;
foreach ($dbHandler ->getAllPostsIDs() as $postsID){
    if(isset($_POST[$postsID])){
        //display danger message
        //and wait confirmation from user
        $dbHandler ->removePost($postsID);
    }
}

//get the posts after the user possibly pressed remove
$view->myPosts = $dbHandler->getAllUserPosts($_SESSION['user_id']);

include "Views/MyPosts.phtml";
?>