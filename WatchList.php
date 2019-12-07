<?php
/**
 * This file is the controller used to handler the
 * situation when the user visits the WatchList page
 */
require_once "Data/DataManager.php";
session_start();
$view = new stdClass();

$userId = $_SESSION['user_id'];
$view->pageTitle = "WatchList";
$dbHandle = DataManager::getInstance();
$view->displayRemoveButton = true;
$view->categories = $dbHandle->getAllCategories();

if(isset($_POST['removeButton'])){
    $encryptedPostID = $_POST['removeValue'];
    foreach ($dbHandle->getAllPostsIDs() as $postID) {
       if(md5($postID) == $encryptedPostID){
           $dbHandle->removePostFromFavorites($postID, $_SESSION['user_id']);
       }
    }

}

$view->favoritePosts = $dbHandle->getWatchList($userId);

include "Views/WatchList.phtml";
?>