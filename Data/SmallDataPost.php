<?php


class SmallDataPost implements JsonSerializable
{
    private $postID;
    private $postTitle;
    private $postImage;
    private $postAuthor;

    public function __construct($db_row)
    {
        $this->postID = $db_row['post_id'];
        $this->postTitle = $db_row['post_title'];
        $this->postImage = $db_row['post_image'];
        $this->postAuthor = $db_row['username'];
    }

    public function jsonSerialize()
    {
        return
            [
                'postID' => $this->getPostID(),
                'postTitle' => $this->getPostTitle(),
                'postImage' => $this->getPostImage(),
                'postAuthor' => $this->getPostAuthor()
        ];
    }

    /**
     * @return mixed
     */
    public function getPostImage()
    {
        return $this->postImage;
    }


    public function getPostID()
    {
        return $this->postID;
    }

    /**
     * @return mixed
     */
    public function getPostAuthor()
    {
        return $this->postAuthor;
    }


    public function getPostTitle()
    {
        return $this->postTitle;
    }

}