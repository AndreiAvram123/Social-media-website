<?php
require_once("Data/Database.php");
require_once("Data/Post.php");

class DatabaseHandler
{
    protected $_dbHandler;

    public static function getInstance()
    {
        return new self();
    }

    public function __construct()
    {
        $this->_dbHandler = Database::getInstance()->getDatabaseConnection();
    }

    public function getAllPosts()
    {
        //Get the posts in chronological order
        $query = "SELECT * FROM forum_posts ORDER BY post_date DESC";
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

}