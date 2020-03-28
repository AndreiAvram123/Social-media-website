<?php
/**
 * This is the controller for the AddPost action
 */
session_start();
require_once "Data/DataManager.php";
require_once "Data/Validator.php";
require_once "utilities/Functions.php";

//create a view and add data to it
$view = new stdClass();
$view->pageTitle = "AddPost";
$dbManager = DataManager::getInstance();
$validator = new Validator();
$view->categories = $dbManager->getAllCategories();

//handle added post action
if (isset($_POST["addPostButton"])) {
    $databaseHandler = DataManager::getInstance();
    //get all the necessary data from the view
    $postTitle = htmlentities($_POST["postTitle"]);
    $postCategoryName = htmlentities($_POST["postCategory"]);
    $postContent = htmlentities($_POST["postContent"]);
    $postDate = date('Y-m-d H:i:s');
    $postImage = $_FILES["fileToUpload"]["name"];




    //check if the captcha code is valid or not
    //returns true if valid
    //else returns error message
    $result = $validator->arePostDetailsValid($postTitle, $postContent, $postImage);
    if ($result === true) {
        $serverImageLocation = $databaseHandler->uploadImageToServer($postImage, $_FILES["fileToUpload"]["tmp_name"], "images/posts/");
        $serverImageLocation = "http://sgb967.poseidon.salford.ac.uk/cms/" . $serverImageLocation;

        $databaseHandler->uploadPost($_SESSION['user_id'],
            $postTitle, $postContent, $postCategoryName, $postDate, $serverImageLocation);
        $view->warningMessage = "You successfully added your post :). Go to main page to check it.";
    } else {
        $view->warningMessage = $result;
    }

}

include "Views/AddPost.phtml";
?>