<?php
require_once "Data/models/LowDataPost.php";

/**
 * This class is used to create Post object
 * that contain the data from a Post row in the
 * database
 *
 */
class Post extends LowDataPost implements JsonSerializable
{
    private $authorName;
    private $authorID;
    private $postDate;
    private $postContent;
    private $postCategoryName;

    private $isFavorite;

    public function __construct($db_row)
    {

        parent::__construct($db_row);
        $this->postDate = $db_row['post_date'];
        $this->postContent = substr($db_row['post_content'], 0, 700);
        $this->postCategoryName = $db_row['post_category_name'];
        $this->authorID = $db_row['post_author_id'];
        $this->authorName = $db_row['username'];
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
                'postAuthor' => $this->getAuthorName(),
                'postCategory' => $this->getCategoryName(),
                'postContent' => $this->getPostContent()
            ];
    }



    public function getAuthorID()
    {
        return $this->authorID;
    }


    public function getCategoryName()
    {
        return $this->postCategoryName;
    }


    public function getAuthorName()
    {
        return $this->authorName;
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