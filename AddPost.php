<?php
include "structure/AddPost.phtml";

if(isset($_POST["addPostButton"])){
    $postCategory = $_POST["postCategory"];
    $postText = $_POST["postText"];
   echo $_FILES["fileToUpload"]["name"];
}
?>