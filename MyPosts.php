<?php
session_start();
require_once "Data/DatabaseHandler.php";
$view = new stdClass();
$view->pageTitle = "My posts";
$view->isUserLoggedIn = isset($_SESSION['user_id']);
$_dbHandler = new DatabaseHandler();
$view ->displayRemoveButton = true;
foreach ($_dbHandler ->getAllPostsIDs() as $postsID){
    if(isset($_POST[$postsID])){
        //display danger message
        //and wait confirmation from user
        $dbHandle ->removePost($postsID);
    }
}

//get the posts after the user possibly pressed remove
$view->myPosts = $_dbHandler->getAllUserPosts($_SESSION['user_id']);

include "Views/MyPosts.phtml";
?>