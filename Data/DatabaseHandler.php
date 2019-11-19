<?php
require_once"Data/Database.php";
require_once "Data/Comment.php";
require_once "Data/Post.php";

class DatabaseHandler
{
    protected $_dbHandler;
    protected $_dbIntance;

    public static function getInstance()
    {
        return new self();
    }

    public function __construct()
    {
        $this->_dbIntance =Database::getInstance();
        $this->_dbHandler = $this->_dbIntance->getDatabaseConnection();
    }

    public function fetchMostRecentPosts()
    {
        //Get the posts in chronological order
        $query = "SELECT * FROM forum_posts ORDER BY post_date DESC ";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $posts = [];
        while ($row = $result->fetch()) {
            $posts[] = new Post($row);
        }
        return $posts;
    }

    public function getUserPasswordFromDB($email)
    {
        $query = "SELECT password FROM users WHERE email = '$email'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        $password = $row['password'];
        return $password;
    }

    public function getAllCategories()
    {
        $query = "SELECT category_name FROM categories";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $categories = [];
        while ($row = $result->fetch()) {
            $categories[] = $row['category_name'];
        }
        return $categories;
    }

    public function uploadPost($postAuthor, $postTitle, $postContent, $postCategoryName, $postDate, $serverImageLocation)
    {
        $query = "INSERT INTO forum_posts VALUES (NULL,'$postAuthor','$postTitle',
        '$postContent','$postCategoryName','$postDate','$serverImageLocation')";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }

    public function uploadFile($target_file, $target_dir)
    {
        if (empty($target_file)) {
            return "";
        }
        $fileValid = true;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        //use the function getimagesize() to check if the image is real or not
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if ($check === false) {
            return "";
        }

        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            return "";
        }
        // Check file size > 5mb
        if ($_FILES["fileToUpload"]["size"] > 5000000) {
            return "";

        }
        if ($fileValid) {
            //once you encrypt the image, the algorithm will also encrypt
            //the file extension. That's why I need to add it as well
            $targetLocation = $target_dir . md5($target_file) . '.' . $imageFileType;
            move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetLocation);
            return $targetLocation;
        }
        return "";
    }

    public function createUser($username, $email, $password, $creationDate)
    {
        $encryptedPassword = md5($password);
        $query = "INSERT INTO users VALUES (NULL,'$username','$email','$encryptedPassword','$creationDate')";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }

    public function getAllPostsIDs()
    {
        $query = "SELECT post_id FROM forum_posts";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $ids = [];
        while ($row = $result->fetch()) {
            $ids[] = $row['post_id'];
        }
        return $ids;
    }

    public function getPostById($postId)
    {
        $query = "SELECT * FROM forum_posts WHERE post_id ='$postId'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        return $result->fetch();
    }

    public function uploadComment($comment_user_id, $comment_post_id, $comment_text, $comment_date, $comment_likes)
    {
        $query = "INSERT INTO comments VALUES(NULL,'$comment_user_id','$comment_post_id'
,'$comment_text','$comment_date','$comment_likes')";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }

    public function getCommentsForPost($postID){
        $query = "SELECT * FROM comments WHERE comment_post_id = '$postID'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $comments = [];
        while($row = $result->fetch()){
            //get the other from the users table using the id key
            //this way if the use changes his username we get the updated version
            $author = $this->getUsernameFromUserID($row['comment_user_id']);
            $comments[] = new Comment($row,$author);
        }
        return $comments;
    }

    public function getUsernameFromUserID($user_id)
    {
        $query = "SELECT username FROM users WHERE user_id = '$user_id'";
        $result = $this ->_dbHandler->prepare($query);
        $result->execute();
        $row = $result ->fetch();
        return $row['username'];
    }

    public function getUserIDFromEmail($email)
    {
        $query ="SELECT user_id FROM  users WHERE email = '$email'";
        $result = $this->_dbHandler ->prepare($query);
        $result -> execute();
        $row = $result->fetch();

        return $row['user_id'];
    }

}