<?php


class Validator
{

    public function arePostDetailsValid($postTitle,$postContent,$postImage)
    {
        $check = $this->isPostTitleValid($postTitle);
        if($check!==true){
            return $check;
        }
        $check = $this ->isPostContentValid($postContent);
        if($check!==true){
            return $check;
        }
        return $this->isImageValid($postImage);
    }
    public function isPostTitleValid($postTitle){
        if(empty($postTitle)){
            return "Please include a title for your post";
        }
        return true;
    }
    public function  isPostContentValid($postContent){
        if(empty($postContent)){
            return "Please include a title for your post";
        }
        return true;
    }

    public  function isImageValid($image_path)
    {
        if (empty($image_path)) {
            return "Please select an image";
        }
        $imageFileType = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
        //use the function getimagesize() to check if the image is real or not
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if ($check === false) {
            //image not real
            return "Please select an image";
        }

        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            return "Please select a valid image type from : jpg, png or jpeg";
        }
        // Check file size > 3mb
        if ($_FILES["fileToUpload"]["size"] > 3000000) {
            return "The size of your image should not be bigger than 3mb";

        }
        return true;

    }
}