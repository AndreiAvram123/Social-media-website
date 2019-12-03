<?php


class Comment
{
    private $comment_id;
    private $comment_user_id;
    private $comment_post_id;
    private $comment_text;
    private $comment_date;
    private $author;

    /**
     * Comment constructor.
     * @param $databaseRow - a comment row from the database
     * @param $author - the author of the Comment
     */
    public function __construct($databaseRow, $author)
    {
      $this->comment_id = $databaseRow['comment_id'];
      $this->comment_user_id = $databaseRow['comment_user_id'];
      $this->comment_post_id = $databaseRow['comment_post_id'];
      $this->comment_text = $databaseRow['comment_text'];
      $this->comment_date = $databaseRow['comment_date'];
      $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
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



}