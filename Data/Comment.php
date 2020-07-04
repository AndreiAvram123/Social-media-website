<?php

/**
 * This class is used to create objects from a
 * comment row in the database
 */
class Comment
{
    private $comment_id;
    private $comment_post_id;
    private $comment_text;
    private $comment_date;
    private $user;

    /**
     *
     * @param $databaseRow - a comment row from the database
     */
    public function __construct($databaseRow)
    {
        $this->comment_id = $databaseRow['comment_id'];
        $this->comment_post_id = $databaseRow['comment_post_id'];
        $this->comment_text = $databaseRow['comment_text'];
        $this->comment_date = $databaseRow['comment_date'];
        $this->user = new User($databaseRow);
    }


    public function getAuthor(): User
    {
        return $this->user;
    }


    public function getCommentId()
    {
        return $this->comment_id;
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