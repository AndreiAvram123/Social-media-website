<?php
require_once "Data/DataManager.php";

session_start();
$view = new stdClass();
$view->pageTitle = "Post";
$view ->isUserLoggedIn = isset($_SESSION['user_id']);
$dbHandler = DataManager::getInstance();

foreach ($dbHandler->getAllPostsIDs() as $buttonId) {
    if (isset($_POST[$buttonId])) {
         $_SESSION['currentPostId'] = $buttonId;
    }
}

$currentPostID = $_SESSION['currentPostId'];
$view ->currentPost =$dbHandler->getPostById($currentPostID);

//check if the user has pressed the add to favorite button
if(isset($_POST['addToFavoriteButton'])){
    //if the user has pressed this button it means that it is logged in
    $userId =$_SESSION['user_id'];
    $dbHandler->addPostToFavorite($currentPostID,$userId);
}
if(isset($_POST['removeFromFavoriteButton'])){
    $userId = $_SESSION['user_id'];
    $dbHandler->removePostFromFavorites($currentPostID, $userId);
}
$view->postBelongsToUser = true;
//first check if the post belongs to the user
if($view->isUserLoggedIn &&
    $dbHandler->getUsernameFromUserID($_SESSION['user_id']) !==$view->currentPost->getAuthorName()) {
$view->postBelongsToUser = false;
//if the user is logged in also check if the post clicked is added to favorites
    if (isset($_SESSION['user_id'])) {
        $isPostFavorite = $dbHandler->isPostAddedToFavorite($currentPostID, $_SESSION['user_id']);
        $view->currentPost->setIsFavorite($isPostFavorite);
    }
}
//handle the new comment
if (isset($_POST['postReviewButton'])) {
    //the post review button is only shown if the user is logged
    //so, we will have an user id in our database
    $comment_user_id = $_SESSION['user_id'];
    $comment_post_id = $_SESSION['currentPostId'];
    $comment_text = $_POST['comment_text'];
    $comment_date = date('Y/m/d');
    $comment_likes = 0;
    $dbHandler->uploadComment($comment_user_id,
        $comment_post_id, $comment_text, $comment_date, $comment_likes);

}

//get the comments for the post
$view -> currentPostComments = $dbHandler->getCommentsForPost($currentPostID);


require_once("Views/CurrentPost.phtml");

?>