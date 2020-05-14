<?php


class Validator
{

    public function arePostDetailsValid($postTitle, $postContent)
    {
        $check = $this->isPostTitleValid($postTitle);
        if ($check !== true) {
            return $check;
        }
        $check = $this->isPostContentValid($postContent);
        if ($check !== true) {
            return $check;
        }
        return true;
    }

    public function isPostTitleValid($postTitle)
    {
        if (empty($postTitle)) {
            return "Please include a title for your post";
        }
        return true;
    }

    public function isPostContentValid($postContent)
    {
        if (empty($postContent)) {
            return "Please include a title for your post";
        }
        return true;
    }

    public function isImageValid($image_path)
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

    /**
     * Check if the login details are
     * in a valid format
     * @param $email
     * @param $password
     * @return bool|string
     */
    public function areLoginCredentialsValid($email, $password)
    {
        if (empty($email)) {
            return "You have not entered an email";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Your email is not valid";
        }
        if (empty($password)) {
            return "You have not entered a password";
        }
        return true;
    }

    /**
     * Check if the image selected by the user is valid
     * and return true if it is valid or
     * an error message if not
     * @param $image
     * @return bool|string
     */
    public function isProfileImageValid($image)
    {

        $imageFileType = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        //use the function getimagesize() to check if the image is real or not
        $check = getimagesize($_FILES["profilePicture"]["tmp_name"]);
        if ($check === false) {
            //image not real
            return "Please select an image";
        }

        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            return "Please select a valid image type from : jpg, png or jpeg";
        }
        // Check file size > 5mb
        if ($_FILES["profilePicture"]["size"] > 5000000) {
            return "The size of your image should not be bigger than 5mb";

        }
        return true;

    }
}