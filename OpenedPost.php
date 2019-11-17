<?php
require_once "Data/DatabaseHandler.php";
require_once "Data/Post.php";
$dbHandler = DatabaseHandler::getInstance();
//look for the post clicked
$openedPost = null;
foreach($dbHandler->getAllPostsIDs() as $buttonId){
    if(isset($_POST[$buttonId])){
        $openedPost = new Post($dbHandler->getPostByID($buttonId));
    }
}
require_once("Views/OpenedPost.phtml");

?>