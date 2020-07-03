<?php
require_once "Data/DataManager.php";
require_once "Data/FriendsDatabase.php";
session_start();

if (isset($_SESSION['user_id'])) {
    $userID = $_SESSION['user_id'];

    $view = new stdClass();
    $db = DataManager::getInstance();
    $friendsDatabase = FriendsDatabase::getInstance();

    $view->friendRequests = $friendsDatabase->getAllFriendRequestsForUser($userID);
    $view->currentUser = $db->getUserById($_SESSION['user_id']);

    include_once "Views/MyProfile.phtml";
} else {
    echo "Nope";
}

?>