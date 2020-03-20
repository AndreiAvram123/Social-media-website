<?php
require_once("Data/FriendsDatabase.php");
require_once("Data/DataManager.php");
require_once("utilities/InputValidator.php");

$dbHandler = DataManager::getInstance();
$responseObject = new stdClass();


if (isset($_GET['recentPosts'])) {
    $data = $dbHandler->getPosts(1);
    echo json_encode($data);
}


if (isset($_GET['friends'])) {
    $userID = null;
    if (isset($_GET['userID']) && ($_GET['userID'] !== "")) {
        $userID = $_GET['userID'];
    }
    if ($userID == null) {
        $responseObject->message = "User id cannot be empty";
        $responseObject->errorMessageID = Constants::$errorMessageURLNotValid;
        echo json_encode($responseObject);
    } else {
        $dbFriends = FriendsDatabase::getInstance();
        //check weather we should fetch the last message as well
        if (isset($_GET['lastMessage'])) {
            $friends = $dbFriends->fetchAllFriendsWithLastMessage($userID);
            echo json_encode($friends);
        }

    }

}


//get the data on a specific post
if (isset($_GET['postID'])) {
    $postID = $_GET['postID'];
    if ($postID !== "") {
        if (isset($_GET['comments'])) {
            echo json_encode($dbHandler->getCommentsForPost($postID));
        } else {
            echo json_encode($dbHandler->getPostById($postID));
        }
    }
}

if (isset($_GET['suggestionQuery'])) {
    $suggestionQuery = $_GET['suggestionQuery'];
    $suggestions = $dbHandler->fetchSearchSuggestions($suggestionQuery, null, null);
    echo json_encode($suggestions);
}
if (isset($_REQUEST['uploadComment'])) {
    $commentUserID = null;
    $commentDate = date('Y-m-d H:i:s');
    $commentContent = null;
    $commentPostID = null;
    $responseObject = new stdClass();
    $responseObject->message = "";

    $json_obj = decodePostData();
    if (isset($json_obj->commentUserID) && ($json_obj->commentUserID !== "")) {
        $commentUserID = $json_obj->commentUserID;
    } else {
        $responseObject->message .= "\n Comment user id cannot be null or empty";
    }

    if (isset($json_obj->commentContent) && $json_obj->commentContent !== "") {
        $commentContent = $json_obj->commentContent;
    } else {
        $responseObject->message .= "\n Comment text  cannot be null or empty";
    }
    if (isset($json_obj->commentPostID) && $json_obj->commentPostID !== "") {
        $commentPostID = $json_obj->commentPostID;
    } else {
        $responseObject->message .= "\n Comment post id  cannot be null or empty";
    }
    if ($commentUserID != null && $commentDate != null && $commentContent != null && $commentPostID != null) {
        $dbHandler->uploadComment($commentUserID, $commentPostID, $commentContent, $commentDate);
        $lastComment = $dbHandler->fetchLastUserComment($commentUserID);
        echo json_encode($lastComment);
    } else {
        echo json_encode($responseObject);
    }


}

if (isset($_REQUEST['uploadPost'])) {
    $json_str = file_get_contents('php://input');
# Get as an object
    $json_obj = json_decode($json_str);
    //upload image
    $base = $json_obj->image;
    // Get file name posted from Android App
    $filename = $json_obj->filename . ".jpeg";
    // Decode Image
    $binary = base64_decode($base);
    header('Content-Type: image/jpeg; charset=utf-8');
    $file = fopen('images/posts/' . $filename, 'wb');
    // Create File
    fwrite($file, $binary);
    fclose($file);
    $postDate = date('Y-m-d H:i:s');

    $dbHandler->uploadPost($json_obj->postAuthorID, $json_obj->postTitle
        , $json_obj->postContent, $json_obj->postCategory, $postDate, "images/posts/" . $filename);

    $lastPost = $dbHandler->fetchLastUserPost($json_obj->postAuthorID);
    echo json_encode($lastPost);
}

if (isset($_REQUEST['savedPosts'])) {
    if (isset($_REQUEST['userID']) && $_REQUEST['userID'] !== "") {
        $favoritePosts = $dbHandler->getWatchList($_REQUEST['userID']);
        echo json_encode($favoritePosts);
    }
}

if (isset($_REQUEST['authenticateThirdPartyAccount'])) {
    if (isset($_REQUEST['email']) && $_REQUEST['email'] !== "") {
        //if the user exists use 1
        $fetchedUser = $dbHandler->getUserFromEmail($_REQUEST['email']);
        if ($fetchedUser != null) {
            $responseObject->responseCode = 1;
            $responseObject->userID = $fetchedUser->getUserId();
            $responseObject->username = $fetchedUser->getUsername();
        } else {
            $responseObject->responseCode = 0;
        }
    } else {
        $responseObject->responseCode = -1;
    }
    echo json_encode($responseObject);

}

if (isset($_REQUEST['addPostToFavorite'])) {
    $postID = $_REQUEST['postID'];
    $userID = $_REQUEST['userID'];
    if ($postID != null && $userID != null) {
        $dbHandler->addPostToFavorite($postID, $userID);
    }
}

if (isset($_REQUEST['createThirdPartyAccount'])) {
    $jsonObject = decodePostData();
    $accountID = $jsonObject->accountID;
    $email = $jsonObject->email;
    $username = $jsonObject->username;
    $profilePictureURL = $jsonObject->profilePicture;
    $date = date('Y-m-d H:i:s');
    if ($accountID != null && $email != null && $username != null && $profilePictureURL != null) {
        //generate random password
        $randomPassword = generateRandomPassword();
        $dbHandler->createUser($username, $email, $randomPassword, $date, $profilePictureURL);
        $responseObject->responseCode = 2;
        $fetchedUser = $dbHandler->getUserFromEmail($email);
        sendEmailWithPassword($email, $randomPassword);
        $responseObject->userID = $fetchedUser->getUserId();
        $responseObject->username = $fetchedUser->getUsername();

    } else {
        $responseObject->responseCode = -1;
    }
    echo json_encode($responseObject);
}

if (isset($_REQUEST['myPosts'])) {
    $userID = null;
    if (isset($_REQUEST['userID']) && $_REQUEST['userID'] !== "") {
        $userID = $_REQUEST['userID'];
    }
    if ($userID != null) {
        $userPosts = $dbHandler->getUserPosts($userID);
        echo json_encode($userPosts);
    } else {
        $responseObject->responseCode = -1;
        echo json_encode($responseObject);
    }
}


function decodePostData()
{
    $json_str = file_get_contents('php://input');
# Get as an object
    return json_decode($json_str);
}

function generateRandomPassword()
{
    $length = 8;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function sendEmailWithPassword($email, $password)
{
    $msg = "You recently authenticated with a third party service such as Google\n";
    $msg .= "If you wish to use your account on the browser version please use the following password \n";
    $msg .= $password;
    mail($email, "Your password", $msg);
}


?>