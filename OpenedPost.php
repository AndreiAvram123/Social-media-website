<?php
require_once "Data/DatabaseHandler.php";
require_once "Data/Post.php";
require_once "SessionHandler.php";

session_start();
$_dbHandler = new DatabaseHandler();
$view = new stdClass();
$view->pageTitle = "Home";
$view -> posts = $_dbHandler->fetchMostRecentPosts();
$view ->isUserLoggedIn = isset($_SESSION['user_email']);
$dbHandler = DatabaseHandler::getInstance();
//find the post that was clicked

foreach ($dbHandler->getAllPostsIDs() as $buttonId) {
    if (isset($_POST[$buttonId])) {
        $_SESSION['currentPostId'] = $buttonId;
    }
}
$view ->curentPost =new Post($dbHandler->getPostByID($_SESSION['currentPostId']));

if (isset($_POST['postReviewButton'])) {
    $comment_user_id = 1;
    $comment_post_id = $_SESSION['currentPostId'];
    $comment_text = $_POST['comment_text'];
    $comment_date = date('Y/m/d');
    $comment_likes = 0;
    $dbHandler->uploadComment($comment_user_id,
        $comment_post_id, $comment_text, $comment_date, $comment_likes);

}

require_once("Views/OpenedPost.phtml");

?>