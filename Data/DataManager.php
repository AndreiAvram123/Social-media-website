<?php
require_once "Data/Database.php";
require_once "Data/Comment.php";
require_once "Data/Post.php";
require_once "Data/User.php";
require_once "Data/models/LowDataPost.php";
require_once "Data/FriendRequest.php";
require_once "utilities/Functions.php";

/**
 * This class is used to handle
 * all SQL interactions with the
 * database (SELECT ,INSERT, UPDATE ,DELETE)
 * Class DataManager
 */
class DataManager
{
    protected $_dbHandler;
    protected $_dbInstance;
    //create a singleton pattern for this as well
    private static $dataManager;
    //define how may posts should be displayed on page
    private $postPerPage = 10;

    //method used to create a singleton pattern
    public static function getInstance()
    {
        if (self::$dataManager !== null) {
            return self::$dataManager;
        } else {
            self::$dataManager = new self();
            return self::$dataManager;
        }

    }

    private function __construct()
    {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandler = $this->_dbInstance->getDatabaseConnection();
    }

    /**
     * This function is used in order to return
     * a given number of posts(how many are defined in the $postPerPage variable)
     * @param $page - give a specific page number and the method will return the
     * posts from that specific page ($page >0)
     * In case there are no posts for that specific page the method will return an empty
     * array
     * @return array
     */
    public function getPosts($page)
    {
        $offset = ($page - 1) * $this->postPerPage;
        //Get the posts in chronological order
        //and in order of pagination
        $query = "SELECT forum_posts.post_id, post_author_id, post_title, post_content, post_category_name, post_date, post_image, username
 FROM forum_posts INNER JOIN users ON users.user_id = forum_posts.post_author_id 
 ORDER BY post_date DESC LIMIT $this->postPerPage OFFSET $offset";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $posts = [];
        while ($row = $result->fetch()) {
            $posts[] = new Post($row);
        }
        return $posts;
    }

    public function getMorePosts($lastPostID)
    {
        $query = "SELECT forum_posts.post_id, post_author_id, post_title, post_content, post_category_name, post_date, post_image, username
 FROM forum_posts INNER JOIN users ON users.user_id = forum_posts.post_author_id  WHERE '$lastPostID' < forum_posts.post_id
 ORDER BY post_date DESC LIMIT $this->postPerPage";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $posts = [];
        while ($row = $result->fetch()) {
            $posts[] = new Post($row);
        }
        return $posts;
    }


    /**
     * This method is used in order to get the user
     * password from the database(encrypted password)
     * @param $email - the email of the user
     * @return
     */
    public function getUserPasswordFromDB($email)
    {
        $query = "SELECT password FROM users WHERE email = :email";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':email', $email);
        $result->execute();
        $row = $result->fetch();
        $password = $row['password'];
        return $password;
    }

    /**
     * This method is used to return all category
     * names from the database and put them in an
     * array
     * @return array of category names
     */
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

    /**
     * This method is used in order to upload a post
     * into the database by gy using the following parameters
     * !!!parameters must not be NULL
     * The function is sql injection protected by using a
     * parameterized query
     * @param $postAuthorId
     * @param $postTitle
     * @param $postContent
     * @param $postCategoryName
     * @param $postDate
     * @param $serverImageLocation
     */
    public function uploadPost($postAuthorId, $postTitle, $postContent, $postCategoryName, $postDate, $serverImageLocation)
    {
        $query = "INSERT INTO forum_posts VALUES (NULL,:postAuthorId,:postTitle,
        :postContent,:postCategoryName,:postDate,:imageLocation)";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':postAuthorId', $postAuthorId);
        $result->bindValue(':postTitle', $postTitle);
        $result->bindValue(':postContent', $postContent);
        $result->bindValue(':postCategoryName', $postCategoryName);
        $result->bindValue(':postDate', $postDate);
        $result->bindValue(':imageLocation', $serverImageLocation);
        $result->execute();
    }

    /**
     * This method is used to create an user in the database
     * by giving the following parameters
     * !!!$imageLocation can be null(in this case the user will be given a default
     * profile picture)
     * The method is sql injection protected as it uses parameterized query
     * @param $username
     * @param $email
     * @param $password
     * @param $creationDate
     * @param $imageLocation
     */
    public function createUser($username, $email, $password, $creationDate, $imageLocation)
    {
        $encryptedPassword = md5($password);
        $query = "INSERT INTO users (user_id,username, email, password, creation_date,profile_picture)
VALUES (NULL,?,?,?,?,?)";

        $result = $this->_dbHandler->prepare($query);
        $result->bindParam(1, $username);
        $result->bindParam(2, $email);
        $result->bindParam(3, $encryptedPassword);
        $result->bindParam(4, $creationDate);
        $result->bindParam(5, $imageLocation);
        $result->execute();

    }

    /**
     * This method is used to upload an image to the server by
     * giving the following parameters
     * The method encrypts the image name as as security reason
     * @param $target_file - the location of the file on the user's computer
     * @param $tempName - the temporary name of the image
     * @param $target_dir - where the image should be place in the server
     * @return string - the image location on the server in order
     * to be stored in a database table
     */
    public function uploadImageToServer($target_file, $tempName, $target_dir)
    {

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        //once you encrypt the image, the algorithm will also encrypt
        //the file extension. That's why I need to add it as well
        $targetLocation = $target_dir . md5($target_file) . '.' . $imageFileType;
        move_uploaded_file($tempName, $targetLocation);
        return $targetLocation;
    }

    /**
     * This method is used to return all the IDs of
     * the available posts in the database
     * @return array
     */
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

    /**
     * This function is used to return a specific post
     * that matches an ID in the database
     * Because the post stores the user_id we use a JOIN
     *in order to dynamically get the username
     * @param $postId
     * @return Post
     */
    public function getPostById($postId)
    {
        $query = "SELECT post_id, post_author_id, post_title, post_content, post_category_name, post_date, post_image,users.username
         FROM forum_posts
         INNER JOIN users ON user_id = post_author_id
         WHERE post_id ='$postId'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        return new Post($row);
    }

    /**
     * This method is used in order to upload a
     * comment into the database by giving the following parameters
     * !!parameters should not be null
     * @param $comment_user_id
     * @param $comment_post_id
     * @param $comment_text
     * @param $comment_date
     */
    public function uploadComment($comment_user_id, $comment_post_id, $comment_text, $comment_date)
    {
        $query = "INSERT INTO comments VALUES(NULL,:commentUserID,:commentPostID
,:commentText,:commentDate)";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':commentUserID', $comment_user_id);
        $result->bindValue(':commentPostID', $comment_post_id);
        $result->bindValue(':commentText', $comment_text);
        $result->bindValue(':commentDate', $comment_date);
        $result->execute();
    }

    /**
     * This method is used to get an array  of comments
     * for a specific post by passing the post id
     * @param $postID
     * @return array
     */
    public function getCommentsForPost($postID)
    {
        $query = "SELECT comment_id, comment_user_id, comment_post_id, comment_text, comment_date, user_id, username, email, password, creation_date, profile_picture ,username
FROM comments  INNER JOIN users ON users.user_id = comments.comment_user_id
WHERE comment_post_id = '$postID'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $comments = [];
        while ($row = $result->fetch()) {
            $comments[] = new Comment($row);
        }
        return $comments;
    }

    /**
     * Return all the comments id stored in the database
     * @return array
     */
    public function getAllCommentsIDs()
    {
        $query = "SELECT comment_id FROM comments";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $commentsId = [];
        while ($row = $result->fetch()) {
            $commentsId[] = $row['comment_id'];
        }
        return $commentsId;
    }

    /**
     * Get the user ID from the user email
     *
     * @param $email
     * @return mixed
     */
    public function getUserFromEmail($email)
    {
        $query = "SELECT * FROM  users WHERE email = :email";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':email', $email);
        $result->execute();
        $row = $result->fetch();
        if ($row != false) {
            return new User($row);
        } else {
            return null;
        }
    }

    /**
     * This function is used to search
     * for specific posts in the database
     * for a given query
     * @param $searchQuery - the query that the user entered
     * @param $category - the category that could have been selected as a filter
     * @param $order - the order that the search results should be displayed
     * @param $maxNumberOfResults - the maximum number of results that should be returned
     * @return array
     */
    public function getSearchResult($searchQuery, $category, $order, $maxNumberOfResults)
    {
        //compose a query in order to search by the title, content or category
        //the query searches in order of priorities
        //due to the fact that in a post has the user id
        //we need to execute a JOIN to get the username

        $query = "SELECT post_id, post_author_id, post_title, post_content, post_category_name, post_date, post_image,username FROM forum_posts
        INNER JOIN users ON users.user_id = forum_posts.post_author_id WHERE";

        //add search filters
        if ($category !== "All") {
            $query = $query . "  post_category_name = '$category' AND ";
        }
        //define what to search for
        $query = $query . "(post_title LIKE :searchQueryTitle 
         OR 
         post_author_id IN (SELECT user_id FROM users WHERE username LIKE :searchQueryAuthor))";

        //sort
        switch ($order) {
            case "Newest posts first":
                $query = $query . " ORDER BY post_date DESC";
                break;
            case "Oldest posts first":
                $query = $query . " ORDER BY post_date ASC";
                break;

        }

        if ($maxNumberOfResults !== "All") {
            $query = $query . " LIMIT $maxNumberOfResults";
        }


        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':searchQueryTitle', '%' . $searchQuery . '%');
        $result->bindValue(':searchQueryAuthor', '%' . $searchQuery . '%');
        $result->execute();
        $posts = [];
        while ($row = $result->fetch()) {
            $posts[] = new Post($row);
        }
        return $posts;
    }

    /**
     * This function adds a post to the favorite list
     * of a specific user
     * @param $post_id
     * @param $user_id
     */
    public
    function addPostToFavorite($post_id, $user_id)
    {
        $query = "INSERT INTO favorite_posts VALUES (:post_id,:user_id)";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':post_id', $post_id);
        $result->bindValue(':user_id', $user_id);
        $result->execute();

    }

    /**
     * Return true if the post is added to the user's watch list or not
     * @param $post_id
     * @param $user_id
     * @return bool
     */
    public function isPostAddedToWatchList($post_id, $user_id)
    {
        $query = "SELECT * from favorite_posts WHERE user_id = '$user_id' AND post_id = '$post_id'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        return $row != null;
    }

    /**
     * Get the watch list of a specific user by passing the
     * $userID
     * @param $userId
     * @return array
     */
    public function getWatchList($userId)
    {
        //get the post ids from the favorite table and then select
        //all the posts from the posts table that have
        //that specific id
        $query = "SELECT  post_id, post_author_id, post_title, post_content, post_category_name, post_date, post_image,username
         FROM forum_posts
         INNER JOIN users ON user_id = post_author_id
         WHERE post_id IN 
        (SELECT post_id from favorite_posts WHERE user_id = :userId)";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':userId', $userId);
        $result->execute();
        $posts = [];
        while ($row = $result->fetch()) {
            $post = new Post($row);
            $post->setAddedToWatchList(true);
            $posts[] = $post;
        }
        return $posts;
    }

    /**
     * @param $postID
     * Use this function in order to remove a specific
     * post from the watchList of a specific
     * user
     * @param $user_id
     */
    public
    function removePostFromFavorites($postID, $user_id)
    {
        $query = "DELETE FROM favorite_posts WHERE user_id = '$user_id' AND 
        post_id = '$postID'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }


    /**
     * This function returns all the users posts
     * in the database by passing the userId
     * @param $user_id
     * @return array
     */
    public function getUserPosts($user_id)
    {
        $query = "SELECT  post_id, post_author_id, post_title, post_content, post_category_name, post_date, post_image,username FROM forum_posts
        INNER JOIN users ON user_id = post_author_id
       WHERE post_author_id = '$user_id' LIMIT 10";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $posts = [];
        while ($row = $result->fetch()) {
            $posts[] = new Post($row);
        }
        return $posts;
    }


    /**
     * This method is used in order to
     * remove a post from the database given the id
     * It makes sure that once removed from the posts table
     * all the corresponding rows in the other tables are removed
     * as well
     * @param $postID
     */
    public function removePost($postID)
    {
        $query = "DELETE FROM forum_posts WHERE post_id = $postID";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $this->removePostOccurrenceInFavorites($postID);
    }

    /**
     * Method used in order to check if the username
     * already exists in the database
     * Return true if the username exists or false if not
     * @param $username
     * @return bool
     */
    public function usernameExists($username)
    {
        $query = "SELECT user_id FROM users WHERE username = :username";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':username', $username);
        $result->execute();
        $row = $result->fetch();
        return $row != null;
    }

    /**
     * Method used to delete a comment from the database
     * @param $commentID
     */
    public function deleteComment($commentID)
    {
        $query = "DELETE FROM comments WHERE comment_id = '$commentID'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }

    /**
     * Return the total number of pages
     * depending on the variable $this->postsPerPage
     * @return int
     */
    public function getNumberOfPages()
    {
        $query = "SELECT COUNT(post_id) FROM forum_posts";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        $totalPosts = $row['COUNT(post_id)'];
        //If the number is multiple of $this->$postsPerPage  return the value
        //Otherwise divide it by 10 and then add 1
        if ($totalPosts % $this->postPerPage == 0) {
            return $totalPosts / $this->postPerPage;
        } else {
            return $totalPosts / $this->postPerPage + 1;
        }
    }

    /**
     * Return all the users Ids in the database
     * @return array
     */
    public function getAllUsersId()
    {
        $query = "SELECT user_id FROM users";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $userIds = [];
        while ($row = $result->fetch()) {
            $userIds[] = $row['user_id'];
        }
        return $userIds;
    }

    /**
     * Return a user from the database by
     * passing the $userId to the method
     * @param $userId
     * @return User
     */
    public function getUserById($userId)
    {
        $query = "SELECT * from users WHERE user_id = '$userId'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        if ($row === false) {
            return null;
        } else {
            return new User($row);
        }

    }


    /**
     * Update the post title in the
     * database of a specific post
     * @param $postID
     * @param $postTitle - !!!must not be null
     */
    public function changePostTitle($postID, $postTitle)
    {
        $query = "UPDATE forum_posts SET post_title = :postTitle WHERE post_id = :postId";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':postTitle', $postTitle);
        $result->bindValue(':postId', $postID);
        $result->execute();
    }

    /**
     * Update the post content of
     * a specific post
     * @param $postID
     * @param $postContent - !!!must not be null
     */
    public function changePostContent($postID, $postContent)
    {
        $query = "UPDATE forum_posts SET post_content = :postContent WHERE post_id = :postId";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':postContent', $postContent);
        $result->bindValue(':postId', $postID);
        $result->execute();
    }

    /**
     * Update the post category
     * of a specific post
     * @param $postID
     * @param $postCategory - !!!must not be null
     */
    public function changePostCategory($postID, $postCategory)
    {
        $query = "UPDATE forum_posts SET post_category_name = :postCategoryName WHERE post_id = :postId";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue('postCategoryName', $postCategory);
        $result->bindValue(':postId', $postID);
        $result->execute();
    }

    /**
     * Update the image
     * of a specific post
     * @param $postID
     * @param $postImage
     */
    public function changePostImage($postID, $postImage)
    {
        $query = "UPDATE forum_posts SET post_image = :postImage WHERE post_id = :postId";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue('postImage', $postImage);
        $result->bindValue(':postId', $postID);
        $result->execute();

    }

    /**
     * Check if the user email exists in the
     * database
     * @param $email
     * @return bool - true if the email exists or false if not
     */
    public function emailExists($email)
    {
        $query = "SELECT user_id FROM users WHERE email = :email";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':email', $email);
        $result->execute();
        $row = $result->fetch();
        return $row != null;
    }

    /**
     *This method is called once
     * a post in deleted in order to delete
     * all the rows in the favorites table that
     * include that post
     * @param $postID
     */
    private function removePostOccurrenceInFavorites($postID)
    {
        $query = "DELETE FROM favorite_posts WHERE post_id = '$postID'";
        $this->executeQuery($query);
    }

    private function executeQuery($query)
    {
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }

    public function fetchSearchSuggestions($searchQuery, $sortDate, $category)
    {
        $query = "SELECT post_id,post_title,post_image FROM forum_posts WHERE post_title LIKE :searchQuery";

        if ($category !== null) {
            $query .= "AND post_category_name = '$category'";
        }
        if ($sortDate !== null) {
            if ($sortDate === "Newest posts first") {
                $query = $query . " ORDER BY post_date DESC";
            } else {
                $query = $query . " ORDER BY post_date";
            }
        }
        $query .= " LIMIT 10";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':searchQuery', $searchQuery . '%');

        $result->execute();
        $suggestions = [];

        while ($row = $result->fetch()) {
            $currentPost = new LowDataPost($row);
            $imageLocation = $currentPost->getPostImage();
            $position = strpos($imageLocation, ".",50);
            $resizedImageLocation = substr_replace($imageLocation, "_resized", $position, 0);
            $currentPost->setPostImage($resizedImageLocation);
            $suggestions[] = $currentPost;

        }
        return $suggestions;


    }


    public function fetchLastUserComment($commentUserID)
    {
        $query = "SELECT * FROM comments 
   INNER JOIN users ON user_id = comment_user_id
WHERE comment_user_id = '$commentUserID' 
ORDER BY comment_id DESC LIMIT 1";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        return new Comment($row);
    }

    public function fetchLastUserPost($postAuthorID)
    {
        $query = "SELECT * FROM forum_posts WHERE post_author_id = '$postAuthorID' ORDER BY post_id DESC LIMIT 1";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        return new Post($row);
    }

    public function uploadResizedImageToServer($originalImageLocation, $tmp_name)
    {
        // Create a new Imagick object
        $imagick = new Imagick(
            $tmp_name);

// Resize the image
        $imagick->resizeImage(70, 40, Imagick::FILTER_LANCZOS, 1);
        $imagick->writeImage("test.jpeg");
    }
}