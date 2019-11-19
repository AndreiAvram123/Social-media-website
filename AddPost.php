<?php
require_once('Data/DatabaseHandler.php');
$view = new stdClass();
$view->pageTitle = "Register";
$view ->isUserLoggedIn = true;

include "Views/AddPost.phtml";

//handle added post
if (isset($_POST["addPostButton"])) {
    $postTitle = $_POST["postTitle"];
    $postCategoryName = $_POST["postCategory"];
    $postContent = $_POST["postContent"];
    $postDate = date('Y/m/d');
    $postAuthor = "unknown";
     $databaseHandler = DatabaseHandler::getInstance();
    if(arePostDetailsValid($postTitle,$postContent)) {
        $serverImageLocation = $databaseHandler->uploadFile($_FILES["fileToUpload"]["name"], "images/");
        if ($serverImageLocation != "") {
            $databaseHandler->uploadPost($postAuthor,
                $postTitle, $postContent, $postCategoryName, $postDate, $serverImageLocation);
            displayWarningMessage("You successfully added your post:). Go to main page to check it.");
        }else{
            displayWarningMessage("Please select a valid image");
        }
    }
}

function arePostDetailsValid($postTitle,$postContent)
{
    if(empty($postTitle)){
        displayWarningMessage("Please include a title for your post");
        return false;
    }
    if(empty($postContent)){
        displayWarningMessage("Please include a title for your post");
        return false;
    }
    return true;
}


?>