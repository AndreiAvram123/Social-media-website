<?php
require "DB.php";
//look for the post clicked
foreach(getAllPostsIDs() as $buttonId){
    if(isset($_POST[$buttonId])){
        $openedPost = getPostByID($buttonId);
    }
}

require_once("Views/ExtendedPost.phtml");
?>