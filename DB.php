<?php require "Post.php";
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'cms';
$connection = mysqli_connect($host,$username,$password,$database);


function getAllPosts(){
    global $connection;
    $query = "SELECT * FROM forum_posts";
    $result = mysqli_query($connection,$query);
    $posts = array();
    while($row = mysqli_fetch_assoc($result)){
        $post = new Post($row['post_author'],$row['post_title']
       ,$row['post_date'], $row['post_content']);
        $posts[] = $post;
    }
    return $posts;
}

function areUserDetailsValid(){
    return false;
}
function createUser(){

}

?>