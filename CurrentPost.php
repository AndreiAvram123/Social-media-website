f<?php
/**
 * This file is used as a controller when the user
 * has pressed the see Post button. It gathers all the necessary
 * data about that specific post and displays it
 */

require_once "Data/DataManager.php";
include_once "utilities/CommonFunctions.php";
session_start();
$view = new stdClass();
$view->pageTitle = "OpenedPost";
$dbHandler = DataManager::getInstance();
$view->categories = $dbHandler->getAllCategories();

//check if the user has pressed the add to favorite button
if (isset($_POST['addToFavoriteButton'])) {
    //if the user has pressed this button it means that it is logged in
    $userId = $_SESSION['user_id'];
    $dbHandler->addPostToFavorite($_SESSION["currentPostId"], $userId);
}
if (isset($_POST['removeFromFavoriteButton'])) {
    $userId = $_SESSION['user_id'];
    $dbHandler->removePostFromFavorites($_SESSION["currentPostId"], $userId);
}


if (isset($_GET["valuePostID"])) {
    $postIDEncrypted = $_GET["valuePostID"];
    //reset the post id set in the session
    $_SESSION["currentPostId"] = null;
    foreach ($dbHandler->getAllPostsIDs() as $postID) {
        if ($postIDEncrypted === CommonFunctions::encodeWithSha512($postID)) {
            //store the post id in the session
            $_SESSION["currentPostId"] = $postID;
        }
    }
}
$currentPostID = $_SESSION["currentPostId"];

if ($currentPostID == null) {
    //security breach
    //the user has changed values in the inspector
    $view->currentPost = null;
    $view->warningMessage = "!!!Any attempt to hack the website could lead to you being banned 
    from the forum!!!";
} else {
    //get the expanded post
    $view->currentPost = $dbHandler->getPostById($currentPostID);
    //check if the user is logged in
    //in order to see if the post belongs to
    //him or if it is added to the watch list
    if (isset($_SESSION['user_id'])) {
        if ($_SESSION['user_id'] !== $view->currentPost->getAuthorID()) {
            $view->postBelongsToUser = false;
            $addedToWatchList = $dbHandler->isPostAddedToWatchList($currentPostID, $_SESSION['user_id']);
            $view->currentPost->setAddedToWatchList($addedToWatchList);
        } else {
            $view->postBelongsToUser = true;
            //an user cannot have his own post to favorites
            $view->currentPost->setAddedToWatchList(false);
        }
    }

    //handle the new comment
    if (isset($_POST['postReviewButton'])) {
        //filter malicious code
        $comment_text = htmlentities($_POST['comment_text']);
        if (!empty($comment_text)) {
            $comment_user_id = $_SESSION['user_id'];
            $comment_post_id = htmlentities($_SESSION['currentPostId']);
            $comment_date = date('Y-m-d H:i:s');
            $dbHandler->uploadComment($comment_user_id,
                $comment_post_id, $comment_text, $comment_date);
            //make sure that users cannot refresh the page and add the comment again
            echo '<meta http-equiv="refresh" content="0; url=CurrentPost.php">';
        } else {
            $view->warningMessage = "Please include some text for your comment!!";
        }
    }

//for security reasons encrypt the comment id
//so it is not visible to users in the inspector
    if (isset($_POST['removeCommentButton'])) {
        $commentIDEncrypted = $_POST['valueCommentID'];
        foreach ($dbHandler->getAllCommentsIDs() as $commentID) {
            if ($commentIDEncrypted === md5($commentID)) {
                $dbHandler->deleteComment($commentID);
            }
        }
    }
    //get the comments for the post
    $currentPostComments = $dbHandler->getCommentsForPost($currentPostID);
    $view->currentPostComments = $currentPostComments;
}
//include the view
include_once("Views/CurrentPost.phtml");

?>