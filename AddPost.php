<?php
session_start();

require_once('Data/DataManager.php');
$view = new stdClass();
$view->pageTitle = "Register";
$view ->isUserLoggedIn = isset($_SESSION['user_id']);

include "Views/AddPost.phtml";

//handle added post
if (isset($_POST["addPostButton"])) {
    $databaseHandler = DataManager::getInstance();
    $postTitle = $_POST["postTitle"];
    $postCategoryName = $_POST["postCategory"];
    $postContent = $_POST["postContent"];
    $postDate = date('Y-m-d H:i:s');
    if(arePostDetailsValid()) {
            $serverImageLocation = $databaseHandler->uploadImage($_FILES["fileToUpload"]["name"], "images/");
            $databaseHandler->uploadPost($_SESSION['user_id'],
                $postTitle, $postContent, $postCategoryName, $postDate, $serverImageLocation);
            displayAlertMessage("You successfully added your post :). Go to main page to check it.");
        }
}

function arePostDetailsValid()
{
    if(empty($postTitle)){
        displayAlertMessage("Please include a title for your post");
        return false;
    }
    if(empty($postContent)){
        displayAlertMessage("Please include a title for your post");
        return false;
    }
    $imageCheck = isImageValid();
    if($imageCheck !==true){
       displayAlertMessage($imageCheck);
       return false;
    }
    return true;
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
?>