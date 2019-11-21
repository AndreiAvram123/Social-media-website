<?php
require_once "Data/Database.php";
require_once "Data/Comment.php";
require_once "Data/Post.php";

class DataManager
{
    protected $_dbHandler;
    protected $_dbInstance;

    public static function getInstance()
    {
        return new self();
    }

    public function __construct()
    {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandler = $this->_dbInstance->getDatabaseConnection();
    }

    /**
     * Return an array of 10 most recent posts
     */
    public function fetchMostRecentPosts()
    {
        //Get the posts in chronological order
        $query = "SELECT * FROM forum_posts ORDER BY post_date DESC LIMIT 10";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $posts = [];
        while ($row = $result->fetch()) {
            $author_name = $this->getUsernameFromUserID($row['post_author_id']);
            $posts[] = new Post($row, $author_name);
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

    public function uploadPost($postAuthorId, $postTitle, $postContent, $postCategoryName, $postDate, $serverImageLocation)
    {
        //todo
        //bind parameters
        $query = "INSERT INTO forum_posts VALUES (NULL,'$postAuthorId','$postTitle',
        '$postContent','$postCategoryName','$postDate','$serverImageLocation')";
        echo $query;
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }

    //todo
    //include error messages
    public function uploadImage($target_file, $target_dir)
    {
//        if (!isset($target_file)) {
//            $fileValid = true;
//            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
//            //use the function getimagesize() to check if the image is real or not
//            $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
//            if ($check === false) {
//                return "";
//            }
//
//            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
//                return "";
//            }
//            // Check file size > 5mb
//            if ($_FILES["fileToUpload"]["size"] > 5000000) {
//                return "";
//
//            }
                 $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                //once you encrypt the image, the algorithm will also encrypt
                //the file extension. That's why I need to add it as well
                $targetLocation = $target_dir . md5($target_file) . '.' . $imageFileType;
                move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetLocation);
                return $targetLocation;

//        }
//        return "";
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
        $row =  $result->fetch();
        $username = $this->getUsernameFromUserID($row['post_author_id']);
        return new Post($row,$username);
    }

    public function uploadComment($comment_user_id, $comment_post_id, $comment_text, $comment_date, $comment_likes)
    {
        $query = "INSERT INTO comments VALUES(NULL,'$comment_user_id','$comment_post_id'
,'$comment_text','$comment_date','$comment_likes')";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }

    public function getCommentsForPost($postID)
    {
        $query = "SELECT * FROM comments WHERE comment_post_id = '$postID'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $comments = [];
        while ($row = $result->fetch()) {
            //get the other from the users table using the id key
            //this way if the use changes his username we get the updated version
            $author = $this->getUsernameFromUserID($row['comment_user_id']);
            $comments[] = new Comment($row, $author);
        }
        return $comments;
    }

    public function getUsernameFromUserID($user_id)
    {
        $query = "SELECT username FROM users WHERE user_id = '$user_id'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        return $row['username'];
    }

    public function getUserIDFromEmail($email)
    {
        $query = "SELECT user_id FROM  users WHERE email = '$email'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();

        return $row['user_id'];
    }

    /**
     * This function is used to search
     * for specific posts in the database
     * for a given query
     *
     * @param $searchQuery
     * @return array
     */
    public function getSearchResult($searchQuery)
    {
        $query = "SELECT * FROM forum_posts WHERE post_title LIKE :searchQuery";
        $result = $this->_dbHandler->prepare($query);
        //use parameterized query to avoid sql injection
        $result->bindValue(':searchQuery', '%' . $searchQuery . '%');
        $result->execute();
        $posts = [];
        while ($row = $result->fetch()) {
            $post_author = $this->getUsernameFromUserID($row['post_author_id']);
            $posts[] = new Post($row, $post_author);
        }
        return $posts;
    }

    /**
     * This function adds a post to the favorite list
     * of a specific user
     * @param $post_id
     * @param $user_id
     */
    public function addPostToFavorite($post_id, $user_id)
    {
        $query = "INSERT INTO favorite_posts VALUES (:post_id,:user_id)";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':post_id', $post_id);
        $result->bindValue(':user_id', $user_id);
        $result->execute();

    }

    public function isPostAddedToFavorite($post_id,$user_id)
    {
        $query = "SELECT * from favorite_posts WHERE user_id = '$user_id' AND post_id = '$post_id'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        if($row){
            return true;
        }else{
            return false;
        }
    }


    public function getFavoritePosts($userId)
    {
        //get the post ids from the favorite table and then select
        //all the posts from the posts table that have
        //that specific id
        $query = "SELECT * FROM forum_posts WHERE post_id IN 
        (SELECT post_id from favorite_posts WHERE user_id = :userId)";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':userId', $userId);
        $result->execute();
        $posts = [];
        while ($row = $result->fetch()) {
            $username = $this->getUsernameFromUserID($row['post_author_id']);
            $post = new Post($row,$username);
            $post->setIsFavorite(true);
            $posts[] = $post;
        }
        return $posts;
    }

    /**
     * @param $postID
     * Use this function in order to remove a specific
     * post from the favorite list of a specific
     * user
     * @param $user_id
     */
    public function removePostFromFavorites($postID, $user_id)
    {
        $query = "DELETE FROM favorite_posts WHERE user_id = '$user_id' AND 
        post_id = '$postID'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }


    public function getAllUserPosts($user_id)
    {
        $query = "SELECT * FROM forum_posts WHERE post_author_id = '$user_id' LIMIT 10";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $posts = [];
        while ($row = $result->fetch()) {
            $username = $this->getUsernameFromUserID($user_id);
            $posts[] = new Post($row, $username);
        }
        return $posts;
    }

    public function removePost($postsID)
    {
        $query = "DELETE FROM forum_posts WHERE post_id = $postsID";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();

    }
    public function usernameExists($username){
        $query = "SELECT user_id FROM users WHERE username = :username";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':username', $username);
        $result->execute();
        $row = $result->fetch();
        return is_null($row);
      }


}