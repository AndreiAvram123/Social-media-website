<?php
session_start();
require_once "Data/DataManager.php";
$view = new stdClass();
$view->pageTitle = "Home";
$view ->isUserLoggedIn = isset($_SESSION['user_id']);
$dbManager = DataManager::getInstance();
if(isset($_POST['addToFriendsButton'])){
    $userIDEncrypted = $_POST['userIdValue'];
    foreach ($dbManager->getAllUsersId() as $userID){
        if($userIDEncrypted === md5($userID)){
            //process add friends request

        }
    }
}

include "Views/Friends.phtml";

?>