<?php
require_once "Data/DataManager.php";

function signUserOut()
{
    unset($_SESSION['user_id']);

}

function addUserDataToSession($userId)
{
    $_SESSION['user_id'] = $userId;
}

function loginUser($email,$enteredPassword)
{
    $databaseHandler = new DataManager();
    $check = areLoginCredentialsValid($email, $enteredPassword);
    if ($check === true) {
        $userPasswordDB = $databaseHandler->getUserPasswordFromDB($email);
        if (!empty($userPasswordDB)) {
            if (md5($enteredPassword) == $userPasswordDB) {
                addUserDataToSession($databaseHandler->getUserIDFromEmail($email));
                return true;
            } else {
                return "Incorrect password";
            }
        } else {
            return "Hmm...Seems like your account does not exist";
        }
    }else{
        return $check;
    }
}

function areLoginCredentialsValid($email, $password)
{
    if (empty($email)) {
        return "You have not entered an email";
    }
    if (empty($password)) {
        return "You have not entered a password";
    }
    return true;
}

function createUser($username, $email, $password, $image, $creationDate)
{
    $databaseHandler = DataManager::getInstance();
    $check = checkRegisterCredentials($username, $email, $password, $image);
    if($check === true) {
        $imageLocation = $databaseHandler->uploadImage($image, "images/");
        $databaseHandler->createUser($username, $email, $password, $creationDate,$imageLocation);
        return true;
    }else {
        return $check;
    }
}
function checkRegisterCredentials($username,$email,$password,$image){
    if (empty($username)) {
        return "You have not entered an email";
    }
    if (empty($email)) {
        return "You have not entered an email";
    }
    if (empty($password)) {
        return "You have not entered a password";
    }
    if(strlen($password) <7){
        return "Your password is not strong enough";
    }
    if(DataManager::getInstance()->usernameExists($username)){
       return "The username already exists";
    }
    if(!is_null($image)){
        return isImageValid($image);
    }
    return true;

}

function isImageValid($image)
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