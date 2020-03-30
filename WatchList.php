<?php

require_once "Data/DataManager.php";
require_once "utilities/Functions.php";
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
       if(Functions::encodeWithSha512($postID) == $encryptedPostID){
           $dbHandle->removePostFromFavorites($postID, $_SESSION['user_id']);
       }
    }

}

$view->favoritePosts = $dbHandle->getWatchList($userId);

include "Views/WatchList.phtml";
?>