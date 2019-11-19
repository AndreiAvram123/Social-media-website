<?php


class Comment
{
    private $comment_id;
    private $comment_user_id;
    private $comment_post_id;
    private $comment_text;
    private $comment_date;
    private $comment_likes;

    public function __construct($row)
    {
      $this->comment_id = $row['comment_id'];
      $this->comment_user_id = $row['comment_user_id'];
      $this->comment_post_id = $row['comment_post_id'];
      $this->comment_text = $row['comment_text'];
      $this->comment_date = $row['comment_date'];
      $this->comment_likes = $row['comment_likes'];
    }


    public function getCommentId()
    {
        return $this->comment_id;
    }


    public function getCommentUserId()
    {
        return $this->comment_user_id;
    }


    public function getCommentPostId()
    {
        return $this->comment_post_id;
    }


    public function getCommentText()
    {
        return $this->comment_text;
    }

    public function getCommentDate()
    {
        return $this->comment_date;
    }


    public function getCommentLikes()
    {
        return $this->comment_likes;
    }


}