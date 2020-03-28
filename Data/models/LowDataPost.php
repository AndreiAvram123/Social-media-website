<?php

/**
 * Class LowDataPost
 * A class that may be used for searching or low
 * data usages
 * It serializes less field comparing to the bigger one
 * Post.php
 */
class LowDataPost implements JsonSerializable
{
    private $postTitle;
    private $postID;
    private $postImage;

    public function __construct($db_row)
    {

        $this->postID = $db_row['post_id'];
        $this->postTitle = $db_row['post_title'];
        $this->postImage = $db_row['post_image'];
    }

    public function getPostTitle()
    {
        return $this->postTitle;
    }

    public function getPostImage()
    {
        return $this->postImage;
    }

    public function getPostID()
    {
        return $this->postID;
    }

    /**
     * @param mixed $postID
     */
    public function setPostID($postID)
    {
        $this->postID = $postID;
    }

    /**
     * @param mixed $postImage
     */
    public function setPostImage($postImage)
    {
        $this->postImage = $postImage;
    }


    public function jsonSerialize()
    {
        return
            [
                'postID' => $this->getPostID(),
                'postTitle' => $this->getPostTitle(),
                'postImage' => $this->getPostImage(),
            ];
    }
}