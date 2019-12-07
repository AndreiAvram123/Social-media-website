<?php
/**
 * This file is the controller that handles the situation when the user
 * enters the MyPosts page
 */
session_start();
require_once "Data/DataManager.php";
//create the view and put data in it
$view = new stdClass();
$view->pageTitle = "My posts";

$dbHandler = DataManager::getInstance();
//we are reusing the PostCard.phtml file, in this situation we want to display the
// remove button
$view->displayRemoveButton = true;

$view->categories = $dbHandler->getAllCategories();
$postIDs= $dbHandler->getAllPostsIDs();
//handle the remove post action
if(isset($_POST['removeButton'])){
    //get the encrypted post id value
    $encryptedPostId = $_POST['removeValue'];
    //loop through the available posts ids and check which post should be removed
    foreach($postIDs as $postId){
        if($encryptedPostId === md5($postId)){
            $dbHandler ->removePost($postId);
        }
    }
}

//get the posts after the user possibly pressed remove
$view->myPosts = $dbHandler->getUserPosts($_SESSION['user_id']);

include "Views/MyPosts.phtml";
?>