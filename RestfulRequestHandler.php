<?php
require_once("Restful/RestfulDatabase.php");
require_once("Data/DataManager.php");

$dbHandler = DataManager::getInstance();

if (isset($_GET['recentPosts'])) {
    $data = $dbHandler->getRecentPostsSmallData();
    echo json_encode($data);
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
    $suggestions = $dbHandler->fetchSearchSuggestionsMobile($suggestionQuery);
    echo json_encode($suggestions);
}
if (isset($_REQUEST['uploadComment'])) {
    $commentUserID = null;
    $commentDate = null;
    $commentText = null;
    $commentPostID = null;
    $responseObject = new stdClass();
    $responseObject->message = "";
    # Get JSON as a string
    $json_str = file_get_contents('php://input');
# Get as an object
    $json_obj = json_decode($json_str);
    if (isset($json_obj->commentUserID) && $json_obj->commentUserID !== "") {
        $commentUserID = $json_obj->commentUserID;
    } else {
        $responseObject->message .= "\n Comment user id cannot be null or empty";
    }
    if (isset($json_obj->commentDate) && $json_obj->commentDate !== "") {
        $commentDate = $json_obj->commentDate;
    } else {
        $responseObject->message .= "\n Comment date  cannot be null or empty";
    }
    if (isset($json_obj->commentText) && $json_obj->commentText !== "") {
        $commentText = $json_obj->commentText;
    } else {
        $responseObject->message .= "\n Comment text  cannot be null or empty";
    }
    if (isset($json_obj->commentPostID) && $json_obj->commentPostID !== "") {
        $commentPostID = $json_obj->commentPostID;
    } else {
        $responseObject->message .= "\n Comment post id  cannot be null or empty";
    }
    if ($commentUserID != null && $commentDate != null && $commentText != null && $commentPostID != null) {
        $dbHandler->uploadComment($commentUserID, $commentPostID, $commentText, $commentDate);
        $responseObject->message = "Success";
    }
    echo json_encode($responseObject);

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

    echo json_encode($json_obj);

}

if (isset($_REQUEST['savedPosts'])) {
    if (isset($_REQUEST['userID']) && $_REQUEST['userID'] !== "") {
        $favoritePosts = $dbHandler->getWatchList($_REQUEST['userID']);
        echo json_encode($favoritePosts);
    }
}

if (isset($_REQUEST['authenticateThirdPartyAccount'])) {

    $responseObject = new stdClass();
    if (isset($_REQUEST['userID']) && $_REQUEST['userID'] !== "") {
        //if the user exists use 1
        $responseObject->responseCode = 1;
        //if the user does not exist use 0

    } else {
        $responseObject->responseCode = -1;
    }
    echo json_encode($responseObject);

}
?>