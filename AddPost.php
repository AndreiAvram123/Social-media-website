<?php
session_start();
require_once "Data/DataManager.php";

$view = new stdClass();
$view->pageTitle = "AddPost";
$view ->isUserLoggedIn = isset($_SESSION['user_id']);
$dbManager = DataManager::getInstance();
$view->categories = $dbManager->getAllCategories();

//handle added post
if (isset($_POST["addPostButton"])) {
    $databaseHandler = DataManager::getInstance();
    $postTitle = $_POST["postTitle"];
    $postCategoryName = $_POST["postCategory"];
    $postContent = $_POST["postContent"];
    $postDate = date('Y-m-d H:i:s');
    //returns true if valid
    //else returns error message
    $result = arePostDetailsValid();
    if($result === true) {
            $serverImageLocation = $databaseHandler->uploadImage($_FILES["fileToUpload"]["name"], "images/");
            $databaseHandler->uploadPost($_SESSION['user_id'],
                $postTitle, $postContent, $postCategoryName, $postDate, $serverImageLocation);
            $view->warningMessage = "You successfully added your post :). Go to main page to check it.";
    }else{
        $view->warningMessage = $result;
    }
}

function arePostDetailsValid()
{
    if(empty($postTitle)){
        return "Please include a title for your post";
    }
    if(empty($postContent)){
        return "Please include a title for your post";
    }
    return isImageValid();
}
function isImageValid()
{
    if (empty($image_path)) {
        return "Please select an image";
    }
        $imageFileType = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
        //use the function getimagesize() to check if the image is real or not
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if ($check === false) {
            //image not real
            return "Please select an image";
        }

        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            return "Please select a valid image type from : jpg, png or jpeg";
        }
        // Check file size > 5mb
        if ($_FILES["fileToUpload"]["size"] > 5000000) {
            return "The size of your image should not be bigger than 5mb";

        }
        return true;

}
include "Views/AddPost.phtml";
?>