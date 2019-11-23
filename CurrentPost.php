<?php
require_once "Data/DataManager.php";

session_start();
$view = new stdClass();
$view->pageTitle = "Post";
$view->isUserLoggedIn = isset($_SESSION['user_id']);
$dbHandler = DataManager::getInstance();

foreach ($dbHandler->getAllPostsIDs() as $buttonId) {
    if (isset($_POST[$buttonId])) {
        $_SESSION['currentPostId'] = $buttonId;
    }
}

$currentPostID = $_SESSION['currentPostId'];
$view->currentPost = $dbHandler->getPostById($currentPostID);

//check if the user has pressed the add to favorite button
if (isset($_POST['addToFavoriteButton'])) {
    //if the user has pressed this button it means that it is logged in
    $userId = $_SESSION['user_id'];
    $dbHandler->addPostToFavorite($currentPostID, $userId);
}
if (isset($_POST['removeFromFavoriteButton'])) {
    $userId = $_SESSION['user_id'];
    $dbHandler->removePostFromFavorites($currentPostID, $userId);
}
$view->postBelongsToUser = true;
//first check if the post belongs to the user
if ($view->isUserLoggedIn &&
    $_SESSION['user_id'] !== $view->currentPost->getAuthorID()) {
    $view->postBelongsToUser = false;
//if the user is logged in also check if the post clicked is added to favorites
    if (isset($_SESSION['user_id'])) {
        $isPostFavorite = $dbHandler->isPostAddedToFavorite($currentPostID, $_SESSION['user_id']);
        $view->currentPost->setIsFavorite($isPostFavorite);
    }
}
//handle the new comment
if (isset($_POST['postReviewButton'])) {
    $comment_text = htmlentities($_POST['comment_text']);
    if (!empty($comment_text)) {
        $comment_user_id = $_SESSION['user_id'];
        $comment_post_id = htmlentities($_SESSION['currentPostId']);
        $comment_date = date('Y/m/d');
        $comment_likes = 0;
        $dbHandler->uploadComment($comment_user_id,
            $comment_post_id, $comment_text, $comment_date, $comment_likes);

    } else {
         $view->warningMessage = "Please include some text for your comment!!";

    }
}

//get the comments for the post
$currentPostComments = $dbHandler->getCommentsForPost($currentPostID);

//check if the remove button has been pressed for a particular
//comment
foreach ($currentPostComments as $comment) {
    //check which comment has been chosen to be removed
    //as a security reason check if the commentUserId is
    //the same the user logged in
    //we do not want to allow users to delete other users'posts
    if (isset($_POST['removeCommentButton'.$comment->getCommentId()]) &&
    $comment->getCommentUserId() === $_SESSION['user_id']) {
        $dbHandler->deleteComment($comment->getCommentId());
   //after removing a comment from the database ,remove it from the array as well
        $index= array_search($comment,$currentPostComments);
        unset($currentPostComments[$index]);
    }
}
$view->currentPostComments = $currentPostComments;


require_once("Views/CurrentPost.phtml");

?>