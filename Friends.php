<?php
session_start();
require_once "Data/DataManager.php";
require_once "Data/FriendsDatabase.php";
$view = new stdClass();

$view->pageTitle = "Friends";
$dbManager = DataManager::getInstance();
$friendsDatabase = FriendsDatabase::getInstance();


$view->categories = $dbManager->getAllCategories();

if(isset($_POST['acceptFriendButton'])){
  $encryptedRequestId = $_POST["senderId"];
  foreach($dbManager->getAllUsersId() as $userId){
      if($encryptedRequestId === md5($userId)){
       $friendsDatabase -> acceptFriendRequest($userId,$_SESSION['user_id']);
      }
  }
}

if(isset($_POST['rejectFriendButton'])){
    $encryptedRequestId = $_POST["senderId"];
    foreach($dbManager->getAllUsersId() as $userId){
        if($encryptedRequestId === md5($userId)){
            $friendsDatabase -> rejectFriendRequest($userId,$_SESSION['user_id']);
        }
    }
}


if (isset($_POST['addToFriendsButton'])) {
    $userIDEncrypted = $_POST['userIdValue'];
    foreach ($dbManager->getAllUsersId() as $userID) {
        if ($userIDEncrypted === md5($userID)) {
            $friendsDatabase->sendFriendRequest($_SESSION['user_id'], $userID);
        }
    }
}
if (isset($_POST['unfriendButton'])) {
    $friendId = $_POST['userIdValue'];
    $friendsDatabase->removeFriend($_SESSION['user_id'], $friendId);
}

$view->friends = $friendsDatabase->getAllFriends($_SESSION['user_id']);
$view->friendRequests = $friendsDatabase->getAllFriendRequestsForUser($_SESSION['user_id']);

include "Views/Friends.phtml";

?>