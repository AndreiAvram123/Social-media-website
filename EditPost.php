<?php
/**
 * This file is the controller used to handle the EditPost action
 */
session_start();
require_once "Data/DataManager.php";
require_once "Data/Validator.php";
//put the details in the view class
$view = new stdClass();
$view->pageTitle = "Edit post";
$dataManager = DataManager::getInstance();
$view->categories = $dataManager->getAllCategories();
$currentPostID = null;

//handle the situation when an user presses save changes button
if (isset($_POST['saveChangesButton'])) {
    $validator = new Validator();
    //get the post that needs to be changed
    $encryptedPostID = $_POST['postID'];
    $post = null;
    //get the post id
    foreach ($dataManager->getAllPostsIDs() as $postID) {
        if (md5($postID) === $encryptedPostID) {
            $currentPostID = $postID;
            $post = $dataManager->getPostById($postID);
        }
    }
    //check which details has been changed
    //assume that the values entered are valid
    $valid = true;

    $title = htmlentities($_POST['postTitle']);
    if ($title !== $post->getPostTitle()) {
        $valid = $validator->isPostTitleValid($title);
        if ($valid === true) {
            //execute query to change title
            $dataManager->changePostTitle($post->getPostID(), $title);
        }
    }
    //don't continue if the other details are not valid
    if ($valid === true) {
        $content = htmlentities($_POST['postContent']);
        if ($content !== $post->getPostContent()) {
            $valid = $validator->isPostContentValid($content);
            if ($valid === true) {
                $dataManager->changePostContent($post->getPostID(), $content);
            }
        }
    }
    if ($valid === true) {
        $category = $_POST['postCategory'];
        if ($category !== $post->getCategoryName()) {
            $dataManager->changePostCategory($post->getPostID(), $category);
        }
    }
    if ($valid === true) {
        $postImage = $_FILES["fileToUpload"]["name"];
        if(!empty($postImage)){

            //check if the user selected an image
            if($postImage ===null) {
                $valid = $validator->isImageValid($_FILES["fileToUpload"]["name"]);
                if ($valid === true) {
                    $imageLocation = $dataManager->uploadImageToServer($_FILES["fileToUpload"]["name"],$_FILES["fileToUpload"]["tmp"],"images/posts");
                    $dataManager->changePostImage($post->getPostID(), $imageLocation);
                }
            }
        }


    }
    if($valid===true){
        //redirect user and do not execute any more code
        echo '<meta http-equiv="refresh" content="0; url=MyPosts.php">';
    }else{
        $view->warningMessage = $valid;;
    }

}
if(isset($_POST['cancelChangesButton'])){
    //redirect the user in case none of the changes should take effect
    echo '<meta http-equiv="refresh" content="0; url=MyPosts.php">';
}

//this happens when the user presses the edit button
if (isset($_GET['valuePostID'])) {
    $encryptedPostID = $_GET['valuePostID'];
//get the post id
    foreach ($dataManager->getAllPostsIDs() as $postID) {
        if (md5($postID) === $encryptedPostID) {
            $currentPostID = $postID;
        }
    }
}


if ($currentPostID != null) {
    $view->currentPost = $dataManager->getPostById($currentPostID);
} else {
    //the $currentPostID will only be null if the
    //use changed values in the inspector
    $view->currentPost = null;
    $view->warningMessage =   $view->warningMessage= "!!!Any attempt to hack the website could lead to you being banned";
}

include_once "Views/EditPost.phtml";


?>