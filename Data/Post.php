<?php
require_once "Data/models/LowDataPost.php";
require_once "Data/User.php";
include_once "utilities/Functions.php";

/**
 * This class is used to create Post object
 * that contain the data from a Post row in the
 * database
 *
 */
class Post extends LowDataPost implements JsonSerializable
{
    private $user;
    private $postDate;
    private $postContent;
    private $postCategoryName;


    private $isFavorite;

    public function __construct($db_row)
    {

        parent::__construct($db_row);
        $this->user = new User($db_row);

        $this->postDate = $db_row['post_date'];
        $this->postContent = utf8_encode($db_row['post_content']);
        $this->postCategoryName = $db_row['post_category_name'];
        $this->isFavorite = FALSE;
    }

    public function jsonSerialize()
    {

        return
            [
                'postID' => (int)$this->getPostID(),
                'postTitle' => $this->getPostTitle(),
                'postImage' => $this->getPostImage(),
                'postDate' => $this->getPostDate(),
                'postAuthor' => $this->getUser()->getUsername(),
                'postCategory' => $this->getCategoryName(),
                'postContent' => $this->getPostContent(),
                'postAuthorID' => $this->user->getUserId(),
                'isFavorite' => $this->isFavorite,
            ];
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }


    public function getCategoryName()
    {
        return $this->postCategoryName;
    }


    public function getPostDate()
    {
        return $this->postDate;
    }


    public function getPostContent()
    {
        return $this->postContent;
    }


    public function getIsFavorite()
    {
        return $this->isFavorite;
    }


    public function setAddedToWatchList($isFavorite)
    {
        $this->isFavorite = $isFavorite;
    }


}