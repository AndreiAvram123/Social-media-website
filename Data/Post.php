<?php
class Post
{
  private $authorName;
  private $authorID;
  private $postTitle;
  private $postDate;
  private $postContent;
  private $postImage;
  private $postCategoryName;
  private $postID;
  private $isFavorite;


    public function __construct($db_row,$authorName){
        $this->postID = $db_row['post_id'];
        $this->postTitle = $db_row['post_title'];
        $this->postDate = $db_row['post_date'];
        $this->postContent = $db_row['post_content'];
        $this->postCategoryName = $db_row['post_category_name'];
        $this->authorID = $db_row['post_author_id'];
        $this->postImage = $db_row['post_image'];
        $this->authorName= $authorName;
        $this->isFavorite = FALSE;
    }

    public function getPostID()
    {
        return $this->postID;
    }

    /**
     * @return mixed
     */
    public function getAuthorID()
    {
        return $this->authorID;
    }




    public function getPostCategoryName()
    {
        return $this->postCategoryName;
    }


    public function getAuthorName()
    {
        return $this->authorName;
    }


    public function getPostTitle()
    {
        return $this->postTitle;
    }


    public function getPostDate()
    {
        return $this->postDate;
    }


    public function getPostContent()
    {
        return $this->postContent;
    }

    public function getPostImage()
    {
        return $this->postImage;
    }

    /**
     * @return mixed
     */
    public function getIsFavorite()
    {
        return $this->isFavorite;
    }

    /**
     * @param mixed $isFavorite
     */
    public function setIsFavorite($isFavorite)
    {
        $this->isFavorite = $isFavorite;
    }


}