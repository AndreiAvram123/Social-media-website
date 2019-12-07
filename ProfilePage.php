<?php
/**
 * This file is the controller to handler the situation
 * when the user visits the profile page of a specific user
 */
session_start();
require_once "Data/DataManager.php";
$view = new stdClass();
$view->pageTitle = "Profile";
$view ->isUserLoggedIn = isset($_SESSION['user_id']);
$dbManger = DataManager::getInstance();

if(isset($_GET['profileButton'])){
    //get the encrypted user id from the view
    $encryptedUserID = $_GET['authorIDValue'];
    //loop through the available users ids
    foreach ($dbManger ->getAllUsersId() as $userId){
        if($encryptedUserID === md5($userId)){

            $view->currentUser = $dbManger->getUserById($userId);
            $view->userPosts = $dbManger ->getUserPosts($userId);
        }
    }
}else {
    $view->currentUser = null;
    $view->warningMessage = "!!!Any attempt to hack the website could lead to you being banned 
    from the forum!!!";
}
include_once "Views/ProfilePage.phtml";
?>