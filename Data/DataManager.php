<?php
require_once "Data/Database.php";
require_once "Data/Comment.php";
require_once "Data/Post.php";
require_once "Data/User.php";

class DataManager
{
    protected $_dbHandler;
    protected $_dbInstance;
    //create a singleton pattern for this as well
    private static $dataManager;
    public static $postPerPage = 10;

    public static function getInstance()
    {
        if (self::$dataManager !== null) {
            return self::$dataManager;
        } else {
            self::$dataManager = new self();
            return self::$dataManager;
        }

    }

    public function __construct()
    {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandler = $this->_dbInstance->getDatabaseConnection();
    }

    public function getPosts($page)
    {     $offset = ($page-1) * self::$postPerPage;
        //Get the posts in chronological order
        //and in order of pagination
          $postPerPage = self::$postPerPage;
        //get the username dynamically from the user id using a join
        $query = "SELECT forum_posts.post_id, post_author_id, post_title, post_content, post_category_name, post_date, post_image, username
 FROM forum_posts INNER JOIN users ON users.user_id = forum_posts.post_author_id 
 ORDER BY post_date DESC LIMIT $postPerPage OFFSET $offset";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $posts = [];
        while ($row = $result->fetch()) {
            //limit the amount of text on the main page
            $row['post_content'] = substr($row['post_content'], 1, 700);
            $posts[] = new Post($row);
        }
        return $posts;
    }

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

    public function createUser($username, $email, $password, $creationDate, $imageLocation)
    {
        $encryptedPassword = md5($password);
        if ($imageLocation === null) {
            $query = "INSERT INTO users (user_id,username, email, password, creation_date)
VALUES (NULL,?,?,?,?)";
        } else {
            $query = "INSERT INTO users (user_id,username, email, password, creation_date,profile_picture)
VALUES (NULL,?,?,?,?,?)";

        }
        $result = $this->_dbHandler->prepare($query);
        $result->bindParam(1, $username);
        $result->bindParam(2, $email);
        $result->bindParam(3, $encryptedPassword);
        $result->bindParam(4, $creationDate);
        if ($imageLocation !== null) {
            $result->bindParam(5, $imageLocation);
        }
        $result->execute();

    }

    public function uploadImageToServer($target_file, $tempName, $target_dir)
    {
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        //once you encrypt the image, the algorithm will also encrypt
        //the file extension. That's why I need to add it as well
        $targetLocation = $target_dir . md5($target_file) . '.' . $imageFileType;
        //$_FILES["fileToUpload"]["tmp_name"]
        move_uploaded_file($tempName, $targetLocation);
        return $targetLocation;
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
         $query = "SELECT post_id, post_author_id, post_title, post_content, post_category_name, post_date, post_image,users.username
         FROM forum_posts
         INNER JOIN users ON user_id = post_author_id
         WHERE post_id ='$postId'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        return new Post($row);
    }

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

    public function getUserIDFromEmail($email)
    {
        $query = "SELECT user_id FROM  users WHERE email = :email";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':email', $email);
        $result->execute();
        $row = $result->fetch();
        return $row['user_id'];
    }

    /**
     * This function is used to search
     * for specific posts in the database
     * for a given query
     * @param $searchQuery
     * @param $category
     * @param $order
     * @return array
     */
    public function getSearchResult($searchQuery, $category, $order,$maxNumberOfResults)
    {
        //compose a query in order to search by the title, content or category
        //the query searches in order of priorities
        //due to the fact that in a post has the user id
        //we need to execute a subquery to get the username

        $query = "SELECT post_id, post_author_id, post_title, post_content, post_category_name, post_date, post_image,username FROM forum_posts 
        INNER JOIN users ON users.user_id = forum_posts.post_author_id";

        //add search filters
        if ($category !== "All") {
            $query = $query . " WHERE post_category_name = $category";
        }
        //define what to search for
        $query = $query . " WHERE post_title LIKE :searchQueryTitle 
         OR post_content LIKE :searchQueryContent OR 
         post_author_id IN (SELECT user_id FROM users WHERE username LIKE :searchQueryAuthor)";

        //sort
        switch ($order) {
            case "Newest posts first":
                $query = $query . " ORDER BY post_date DESC";
                break;
            case "Oldest posts first":
                $query = $query . " ORDER BY post_date ASC";
                break;

        }

        if($maxNumberOfResults!=="All"){
         $query = $query . "LIMIT $maxNumberOfResults";
        }


        $result = $this->_dbHandler->prepare($query);
        //use parameterized query to avoid sql injection
        $result->bindValue(':searchQueryTitle', '%' . $searchQuery . '%');
        $result->bindValue(':searchQueryContent', '%' . $searchQuery . '%');
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

    public function isPostAddedToFavorite($post_id, $user_id)
    {
        $query = "SELECT * from favorite_posts WHERE user_id = '$user_id' AND post_id = '$post_id'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        return $row != null;
    }


    public
    function getFavoritePosts($userId)
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
    public
    function removePostFromFavorites($postID, $user_id)
    {
        $query = "DELETE FROM favorite_posts WHERE user_id = '$user_id' AND 
        post_id = '$postID'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }


    public
    function getAllUserPosts($user_id)
    {
        $query = "SELECT  post_id, post_author_id, post_title, post_content, post_category_name, post_date, post_image,username
        INNER JOIN users ON user_id = post_author_id
        FROM forum_posts WHERE post_author_id = '$user_id' LIMIT 10";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $posts = [];
        while ($row = $result->fetch()) {

            $posts[] = new Post($row, $username);
        }
        return $posts;
    }

    public
    function removePost($postsID)
    {
        $query = "DELETE FROM forum_posts WHERE post_id = $postsID";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();

    }

    public
    function usernameExists($username)
    {
        $query = "SELECT user_id FROM users WHERE username = :username";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':username', $username);
        $result->execute();
        $row = $result->fetch();
        return $row != null;
    }

    public
    function deleteComment($commentID)
    {
        $query = "DELETE FROM comments WHERE comment_id = '$commentID'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }

    public function getNumberOfPages()
    {
        $query = "SELECT COUNT(post_id) FROM forum_posts";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        $totalPosts = $row['COUNT(post_id)'];
        //I chose to display 10 posts per page
        //If the number is multiple of 10 just return the value
        //Otherwise divide it by 10 and then add 1
        if ($totalPosts % self::$postPerPage == 0) {
            return $totalPosts / self::$postPerPage;
        } else {
            return $totalPosts / self::$postPerPage + 1;
        }
    }

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

    public function getUserById($userId)
    {
        $query = "SELECT * from users WHERE user_id = '$userId'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        return new User($row);
    }

    public function addToFriendList($currentUserId, $userId)
    {
        $query = "INSERT INTO friends VALUES ('$currentUserId','$userId')";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
    }

    public function getAllFriends($user_id)
    {
        $query = "SELECT * FROM users WHERE user_id IN 
        (SELECT user2_id from friends WHERE user1_id = '$user_id')";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $friends = [];
        while ($row = $result->fetch()) {
            $friends[] = new User($row);
        }
        return $friends;
    }

    public function removeFriend($user_id, $friendId)
    {
        $query = "DELETE FROM friends WHERE user1_id ='$user_id' AND user2_id = '$friendId'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();

    }

    public function changePostTitle($postID, $postTitle)
    {
        $query = "UPDATE forum_posts SET post_title = :postTitle WHERE post_id = :postId";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':postTitle', $postTitle);
        $result->bindValue(':postId', $postID);
        $result->execute();
    }

    public function changePostContent($postID, $postContent)
    {
        $query = "UPDATE forum_posts SET post_content = :postContent WHERE post_id = :postId";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':postContent', $postContent);
        $result->bindValue(':postId', $postID);
        $result->execute();
    }

    public function changePostCategory($postID, $postCategory)
    {
        $query = "UPDATE forum_posts SET post_category_name = :postCategoryName WHERE post_id = :postId";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue('postCategoryName', $postCategory);
        $result->bindValue(':postId', $postID);
        $result->execute();
    }

    public function changePostImage($postID, $postImage)
    {
        $query = "UPDATE forum_posts SET post_image = :postImage WHERE post_id = :postId";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue('postImage', $postImage);
        $result->bindValue(':postId', $postID);
        $result->execute();

    }

    public function emailExists($email)
    {
        $query = "SELECT user_id FROM users WHERE email = :email";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':email', $email);
        $result->execute();
        $row = $result->fetch();
        return $row != null;
    }

}