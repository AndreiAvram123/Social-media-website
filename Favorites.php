<?php
 require_once "Data/DatabaseHandler.php";
session_start();
$view = new stdClass();
$view->isUserLoggedIn = isset($_SESSION['user_id']);
$userId = $_SESSION['user_id'];
$view->pageTitle = "Favorite posts";
$dbHandle= new DatabaseHandler();
$view ->displayRemoveButton = true;
$view->favoritePosts =$dbHandle->getFavoritePosts($userId);
include "Views/Favorites.phtml";
foreach ($dbHandle ->getAllPostsIDs() as $postsID){
    if(isset($_POST[$postsID])){
       $dbHandle ->removePostFromFavorites($_SESSION['user_id'],$postsID);
    }

}

?>