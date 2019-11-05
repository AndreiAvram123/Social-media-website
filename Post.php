<?php

class Post
{
  public $authorName;
  public $postTitle;
  public $postDate;
  public $postContent;

    /**
     * Post constructor.
     * @param $authorName
     * @param $postTitle
     * @param $postDate
     * @param $postContent
     */
    public function __construct($authorName, $postTitle, $postDate, $postContent)
    {
        $this->authorName = $authorName;
        $this->postTitle = $postTitle;
        $this->postDate = $postDate;
        $this->postContent = $postContent;
    }

    /**
     * @return mixed
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    /**
     * @return mixed
     */
    public function getPostTitle()
    {
        return $this->postTitle;
    }

    /**
     * @return mixed
     */
    public function getPostDate()
    {
        return $this->postDate;
    }

    /**
     * @return mixed
     */
    public function getPostContent()
    {
        return $this->postContent;
    }


}