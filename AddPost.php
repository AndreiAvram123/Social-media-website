<?php
session_start();
require_once "Data/DataManager.php";
require_once "Data/Validator.php";
$view = new stdClass();
$view->pageTitle = "AddPost";
$view ->isUserLoggedIn = isset($_SESSION['user_id']);
$dbManager = DataManager::getInstance();
$validator = new Validator();
$view->categories = $dbManager->getAllCategories();

//handle added post
if (isset($_POST["addPostButton"])) {
    $databaseHandler = DataManager::getInstance();
    $postTitle = $_POST["postTitle"];
    $postCategoryName = $_POST["postCategory"];
    $postContent = $_POST["postContent"];
    $postDate = date('Y-m-d H:i:s');
    $postImage = $_FILES["fileToUpload"]["name"];
    //returns true if valid
    //else returns error message
    $result = $validator->arePostDetailsValid($postTitle,$postContent,$postImage);
    if($result === true) {
            $serverImageLocation = $databaseHandler->uploadImageToServer($postImage);
            $databaseHandler->uploadPost($_SESSION['user_id'],
                $postTitle, $postContent, $postCategoryName, $postDate, $serverImageLocation);
            $view->warningMessage = "You successfully added your post :). Go to main page to check it.";
    }else{
        $view->warningMessage = $result;
    }
}

include "Views/AddPost.phtml";
?>