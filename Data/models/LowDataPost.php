<?php


class LowDataPost implements JsonSerializable
{
    private $postTitle;
    private $postID;
    private $postImage;

    public function __construct($db_row)
    {
        $this->postID = md5($db_row['post_id']);
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




    public function jsonSerialize()
    {
        return
            [
                'postID' =>  $this->getPostID(),
                'postTitle' => $this->getPostTitle(),
                'postImage' => $this->getPostImage(),
            ];
    }
}