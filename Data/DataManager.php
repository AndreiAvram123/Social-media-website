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

    public function getPosts($offset)
    {
        //Get the posts in chronological order
        //and in order of pagination
        $query = "SELECT * FROM forum_posts ORDER BY post_date DESC  LIMIT 10  OFFSET $offset";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $posts = [];
        while ($row = $result->fetch()) {
            $author_name = $this->getUsernameFromUserID($row['post_author_id']);
            //limit the amount of text on the main page
            $row['post_content'] = substr($row['post_content'], 1, 700);
            $posts[] = new Post($row, $author_name);
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
        $result->bindValue(':serverImageLocation', $serverImageLocation);
        $result->execute();
    }

    public function uploadImageToServer($target_file)
    {
        $target_dir = "images/";
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        //once you encrypt the image, the algorithm will also encrypt
        //the file extension. That's why I need to add it as well
        $targetLocation = $target_dir . md5($target_file) . '.' . $imageFileType;
        move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetLocation);
        return $targetLocation;
    }


    public function createUser($username, $email, $password, $creationDate, $image)
    {
        $encryptedPassword = md5($password);
        if (!is_null($image)) {
            $query = "INSERT INTO users VALUES (NULL,:username,:email,:encryptedPassword,:creationDate,:profilePicture)";
        } else {
            $query = "INSERT INTO users VALUES (NULL,:username,:email,:encryptedPassword,:creationDate)";
        }
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':username', $username);
        $result->bindValue(':email', $email);
        $result->bindValue(':encryptedPassword', $encryptedPassword);
        $result->bindValue(':creationDate', $creationDate);
        if (!is_null()) {
            $result->bindValue(':profilePicture', $image);
        }
        $result->execute();

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
        $row = $result->fetch();
        $username = $this->getUsernameFromUserID($row['post_author_id']);
        return new Post($row, $username);
    }

    public function uploadComment($comment_user_id, $comment_post_id, $comment_text, $comment_date, $comment_likes)
    {
        $query = "INSERT INTO comments VALUES(NULL,:commentUserID,:commentPostID
,:commentText,:commentDate,:commentLikes)";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':commentUserID', $comment_user_id);
        $result->bindValue(':commentPostID', $comment_post_id);
        $result->bindValue(':commentText', $comment_text);
        $result->bindValue(':commentDate', $comment_date);
        $result->bindValue(':commentLikes', $comment_likes);
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

    public
    function getUsernameFromUserID($user_id)
    {
        $query = "SELECT username FROM users WHERE user_id = '$user_id'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        return $row['username'];
    }

    public
    function getUserIDFromEmail($email)
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
     *
     * @param $searchQuery
     * @return array
     */
    public function getSearchResult($searchQuery)
    {
        //compose a query in order to search by the title, content or category
        //the query searches in order of priorities
        //post_title,post_content,author_name and post_category_name
        //due to the fact that in a post we store a post_author_id
        //we need to execute a subquery to get the authors name
        $query = "SELECT * FROM forum_posts WHERE post_title LIKE :searchQueryTitle 
     OR post_content LIKE :searchQueryContent OR post_author_id IN (SELECT user_id FROM users WHERE username LIKE :searchQueryAuthor)
     OR post_category_name LIKE :searchQueryCategory LIMIT 15 ";

        $result = $this->_dbHandler->prepare($query);
        //use parameterized query to avoid sql injection
        $result->bindValue(':searchQueryTitle', '%' . $searchQuery . '%');
        $result->bindValue(':searchQueryContent', '%' . $searchQuery . '%');
        $result->bindValue(':searchQueryAuthor', '%' . $searchQuery . '%');
        $result->bindValue(':searchQueryCategory', '%' . $searchQuery . '%');
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
    public
    function addPostToFavorite($post_id, $user_id)
    {
        $query = "INSERT INTO favorite_posts VALUES (:post_id,:user_id)";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':post_id', $post_id);
        $result->bindValue(':user_id', $user_id);
        $result->execute();

    }

    public
    function isPostAddedToFavorite($post_id, $user_id)
    {
        $query = "SELECT * from favorite_posts WHERE user_id = '$user_id' AND post_id = '$post_id'";
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        $row = $result->fetch();
        if ($row) {
            return true;
        } else {
            return false;
        }
    }


    public
    function getFavoritePosts($userId)
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
            $post = new Post($row, $username);
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
        if ($row) {
            return true;
        } else {
            return false;
        }
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
        if ($totalPosts % 10 == 0) {
            return $totalPosts / 10;
        } else {
            return $totalPosts / 10 + 1;
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

    public function changePostTitle($postID,$postTitle)
    {
        $query = "UPDATE forum_posts SET post_title = :postTitle WHERE post_id = :postId";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':postTitle', $postTitle);
        $result->bindValue(':postId',$postID);
        $result->execute();
    }
    public function changePostContent($postID,$postContent)
    {
        $query = "UPDATE forum_posts SET post_content = :postContent WHERE post_id = :postId";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':postContent', $postContent);
        $result->bindValue(':postId',$postID);
        $result->execute();
    }
    public function changePostCategory($postID,$postCategory)
    {
        $query = "UPDATE forum_posts SET post_category_name = :postCategoryName WHERE post_id = :postId";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue('postCategoryName', $postCategory);
        $result->bindValue(':postId',$postID);
        $result->execute();
    }
    public function changePostImage($postID,$postImage,$oldPostImage)
    {
        $query = "UPDATE forum_posts SET post_image = :postImage WHERE post_id = :postId";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue('postImage', $postImage);
        $result->bindValue(':postId',$postID);
        $result->execute();
        //delete old image from the server
        //todo
    }

}