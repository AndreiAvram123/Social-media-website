<?php
class Post
{
  private $authorName;
  private $postTitle;
  private $postDate;
  private $postContent;
  private $postImage;
  private $postCategoryName;

    /**
     * Post constructor.
     * @param $authorName
     * @param $postTitle
     * @param $postDate
     * @param $postContent
     * @param $postCategoryName
     * @param $postImage
     */
    public function __construct($authorName, $postTitle, $postDate, $postContent,$postCategoryName,$postImage)
    {
        $this->authorName = $authorName;
        $this->postTitle = $postTitle;
        $this->postDate = $postDate;
        $this->postContent = $postContent;
        $this->postCategoryName = $postCategoryName;
        $this->postImage = $postImage;

    }

    /**
     * @return mixed
     */
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

    /**
     * @return mixed
     */
    public function getPostImage()
    {
        return $this->postImage;
    }



}