<?php
session_start();
require_once "Data/DataManager.php";
require_once "Data/FriendsDatabase.php";
require_once "utilities/Functions.php";
$view = new stdClass();

$view->pageTitle = "Friends";
$dbManager = DataManager::getInstance();
$friendsDatabase = FriendsDatabase::getInstance();


$view->categories = $dbManager->getAllCategories();

if(isset($_POST['acceptFriendButton'])){
  $encryptedRequestId = $_POST["senderId"];
  foreach($dbManager->getAllUsersId() as $userId){
      if($encryptedRequestId === Functions::encodeWithSha512($userId)){
       $friendsDatabase -> acceptFriendRequest($userId,$_SESSION['user_id']);
      }
  }
}

if(isset($_POST['rejectFriendButton'])){
    $encryptedRequestId = $_POST["senderId"];
    foreach($dbManager->getAllUsersId() as $userId){
        if($encryptedRequestId === Functions::encodeWithSha512($userId)){
            $friendsDatabase -> rejectFriendRequest($userId,$_SESSION['user_id']);
        }
    }
}


if (isset($_POST['addToFriendsButton'])) {
    $userIDEncrypted = $_POST['userIdValue'];
    foreach ($dbManager->getAllUsersId() as $userID) {
        if ($userIDEncrypted === Functions::encodeWithSha512($userID)) {
            $friendsDatabase->sendFriendRequest($_SESSION['user_id'], $userID);
        }
    }
}
if (isset($_POST['unfriendButton'])) {
    $encryptedUserID = $_POST['userIdValue'];
    foreach ($dbManager->getAllUsersId() as $userID) {
        if ($encryptedUserID === Functions::encodeWithSha512($userID)) {
            $friendsDatabase->removeFriend($_SESSION['user_id'], $userID);
        }
    }

}

$view->friends = $friendsDatabase->getAllFriends($_SESSION['user_id']);
$view->friendRequests = $friendsDatabase->getAllFriendRequestsForUser($_SESSION['user_id']);

include "Views/Friends.phtml";

?>