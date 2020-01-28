<?php
session_start();
require_once "Data/DataManager.php";
$view = new stdClass();
$view->pageTitle = "Home";
$dbManager = DataManager::getInstance();
$view->categories = $dbManager->getAllCategories();
if(isset($_POST['addToFriendsButton'])){
    $userIDEncrypted = $_POST['userIdValue'];
    foreach ($dbManager->getAllUsersId() as $userID){
        if($userIDEncrypted === md5($userID)){
            $dbManager->addToFriendList($_SESSION['user_id'],$userID);
        }
    }
}
if(isset($_POST['unfriendButton'])){
    $friendId = $_POST['userIdValue'];
    $dbManager->removeFriend($_SESSION['user_id'],$friendId);
}

$view ->friends = $dbManager->getAllFriends($_SESSION['user_id']);

include "Views/Friends.phtml";

?>