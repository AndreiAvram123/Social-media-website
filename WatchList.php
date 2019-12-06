<?php
require_once "Data/DataManager.php";
session_start();
$view = new stdClass();

$userId = $_SESSION['user_id'];
$view->pageTitle = "Favorite posts";
$dbHandle = DataManager::getInstance();
$view->displayRemoveButton = true;

foreach ($dbHandle->getAllPostsIDs() as $postsID) {
    if (isset($_POST[$postsID])) {
        $dbHandle->removePostFromFavorites($postsID, $_SESSION['user_id']);
    }

}
$view->favoritePosts = $dbHandle->getFavoritePosts($userId);

include "Views/WatchList.phtml";
?>