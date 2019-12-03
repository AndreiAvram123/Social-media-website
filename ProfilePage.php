<?php
session_start();
require_once "Data/DataManager.php";
$view = new stdClass();
$view->pageTitle = "Profile";
$view ->isUserLoggedIn = isset($_SESSION['user_id']);
$dbManger = DataManager::getInstance();

if(isset($_GET['profileButton'])){
    $encryptedUserID = $_GET['authorIDValue'];
    foreach ($dbManger ->getAllUsersId() as $userId){
        if($encryptedUserID === md5($userId)){

            $view->currentUser = $dbManger->getUserById($userId);
            $view->userPosts = $dbManger ->getAllUserPosts($userId);
        }
    }
}else {
    $view->currentUser = null;
    $view->warningMessage = "!!!Any attempt to hack the website could lead to you being banned 
    from the forum!!!";
}
include_once "Views/ProfilePage.phtml";
?>