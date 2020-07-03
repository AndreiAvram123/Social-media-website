<?php

session_start();
require_once "Data/DataManager.php";
require_once "utilities/Functions.php";
require_once "Data/FriendsDatabase.php";
require_once "Data/SessionManager.php";

$view = new stdClass();
$view->pageTitle = "Profile";
$view->isUserLoggedIn = isset($_SESSION['user_id']);
$dbManger = DataManager::getInstance();


if (isset($_POST['signOutButton'])) {
    SessionManager::getInstance()->signUserOut();
    $view->redirectHome = true;
}

if (isset($_POST['addToFriendsButton'])) {
    $encryptedUserID = $_POST['userIdValue'];
    //loop through the available users ids
    foreach ($dbManger->getAllUsersId() as $userId) {
        if ($encryptedUserID === Functions::encodeWithSha512($userId)) {
            FriendsDatabase::getInstance()->sendFriendRequest($_SESSION['user_id'], $userId);
            $view->currentUser = $dbManger->getUserById($userId);
            $view->userPosts = $dbManger->getUserPosts($userId);
        }
    }
}
if (isset($_GET['authorIDValue'])) {
    //get the encrypted user id from the view
    $encryptedUserID = $_GET['authorIDValue'];
    //loop through the available users ids
    foreach ($dbManger->getAllUsersId() as $userId) {
        if ($encryptedUserID === Functions::encodeWithSha512($userId)) {
            $view->currentUser = $dbManger->getUserById($userId);
            $view->userPosts = $dbManger->getUserPosts($userId);
        }
    }
}


include_once "Views/ProfilePage.phtml";
?>