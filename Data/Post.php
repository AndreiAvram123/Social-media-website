<?php
class Post
{
  private $authorName;
  private $postTitle;
  private $postDate;
  private $postContent;
  private $postImage;
  private $postCategoryName;
  private $postID;


    public function __construct($db_row){
        $this->postID = $db_row['post_id'];
        $this->authorName = $db_row['post_author'];
        $this->postTitle = $db_row['post_title'];
        $this->postDate = $db_row['post_date'];
        $this->postContent = $db_row['post_content'];
        $this->postCategoryName = $db_row['post_category_name'];
        $this->postImage = $db_row['post_image'];
    }

    public function getPostID()
    {
        return $this->postID;
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



}