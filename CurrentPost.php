<?php
require_once "Data/DatabaseHandler.php";
require_once "Data/Post.php";
require_once "SessionHandler.php";
session_start();

$_dbHandler = new DatabaseHandler();
$view = new stdClass();
$view->pageTitle = "Home";
$view -> posts = $_dbHandler->fetchMostRecentPosts();

$view ->isUserLoggedIn = isset($_SESSION['user_id']);
$dbHandler = DatabaseHandler::getInstance();


//find the post that was clicked
foreach ($dbHandler->getAllPostsIDs() as $buttonId) {
    if (isset($_POST[$buttonId])) {
        $_SESSION['currentPostId'] = $buttonId;
    }
}
$currentPostID = $_SESSION['currentPostId'];
$view ->currentPost =new Post($dbHandler->getPostByID($currentPostID));
$view -> currentPostComments = $dbHandler->getCommentsForPost($currentPostID);


//handle the new post
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

require_once("Views/CurrentPost.phtml");

?>