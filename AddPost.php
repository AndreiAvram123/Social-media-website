<?php
require "DB.php";
include "structure/AddPost.phtml";

if (isset($_POST["addPostButton"])) {
    $postTitle = $_POST["postTitle"];
    $postCategoryName = $_POST["postCategory"];
    $postContent = $_POST["postContent"];
    $postDate = date('Y/m/d');
    $postAuthor = "unknown";

    $postImage = $_FILES["fileToUpload"]["name"];
    $target_dir = "images/";
    $imageFileType = strtolower(pathinfo($postImage, PATHINFO_EXTENSION));

    if (uploadFile($postImage, $target_dir)) {
       $post = new Post($postAuthor,$postTitle,$postDate,$postContent,
           $postCategoryName, md5($postImage) . '.' . $imageFileType);
       insertPostToDatabase($post);
    }

}

function uploadFile($target_file, $target_dir)
{
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if (!$check) {
        //fake image
        return false;
    }

    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        return false;
    }
    // Check file size > 5mb
    if ($_FILES["fileToUpload"]["size"] > 5000000) {
        echo "Sorry, your file is too large.";
        return false;
    }
    //encrypt the image name and then add the extension
    $targetLocation = $target_dir . md5($target_file) . '.' . $imageFileType;
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetLocation)) {
        return true;
    } else {
        return false;
    }

}

?>