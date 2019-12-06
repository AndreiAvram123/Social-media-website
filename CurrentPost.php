<?php
require_once "Data/DataManager.php";
session_start();
$view = new stdClass();
$view->pageTitle = "OpenedPost";
$view->isUserLoggedIn = isset($_SESSION['user_id']);
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


if (isset($_GET["OpenPostButton"])) {
    $postIDEncrypted = $_GET["valuePostID"];
    //reset the post id set in the session
    $_SESSION["currentPostId"] = null;
    foreach ($dbHandler->getAllPostsIDs() as $postID) {
        if ($postIDEncrypted === md5($postID)) {
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
    $view->currentPost = $dbHandler->getPostById($currentPostID);

    if ($view->isUserLoggedIn) {
        if ($_SESSION['user_id'] !== $view->currentPost->getAuthorID()) {
            $view->postBelongsToUser = false;
            $isPostFavorite = $dbHandler->isPostAddedToFavorite($currentPostID, $_SESSION['user_id']);
            $view->currentPost->setIsFavorite($isPostFavorite);
        } else {
            $view->postBelongsToUser = true;
            //an user cannot have his own post to favorites
            $view->currentPost->setIsFavorite(false);
        }
    }

//handle the new comment
    if (isset($_POST['postReviewButton'])) {
        $comment_text = htmlentities($_POST['comment_text']);
        if (!empty($comment_text)) {
            $comment_user_id = $_SESSION['user_id'];
            $comment_post_id = htmlentities($_SESSION['currentPostId']);
            $comment_date = date('Y/m/d');
            $dbHandler->uploadComment($comment_user_id,
                $comment_post_id, $comment_text, $comment_date);
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

require_once("Views/CurrentPost.phtml");

?>