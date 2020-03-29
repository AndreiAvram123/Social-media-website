<?php

require_once "Data/DataManager.php";
require_once "Data/Validator.php";
require_once "utilities/Functions.php";
require_once("Api/ApiKeyManager.php");

$dbManager = DataManager::getInstance();
$validator = new Validator();

$responseObject = new stdClass();
$requestAccepted = false;

if (isset($_REQUEST['apiKey'])) {
    $apiManager = ApiKeyManager::getInstance();
    $requestAccepted = $apiManager->isRequestAccepted($_REQUEST['apiKey'], $_SERVER['REMOTE_ADDR']);
}


if ($requestAccepted == true) {
    $base = $_REQUEST['imageData'];
    $filename = md5($_REQUEST['imageName']) . ".jpeg";
    $fileLocation = 'images/posts/' . $filename;
    $binary = base64_decode($base);
    header('Content-Type: image/jpeg; charset=utf-8');
    $file = fopen($fileLocation, 'wb');
    fwrite($file, $binary);
    fclose($file);


    $resizedBase = $_REQUEST['imageResizedData'];
    $resizedFilename = md5($_REQUEST['imageName']) . "_resized" . ".jpeg";
    $resizedFileLocation = 'images/posts/' . $resizedFilename;
    $binary = base64_decode($resizedBase);
    header('Content-Type: image/jpeg; charset=utf-8');
    $file = fopen($resizedFileLocation, 'wb');
    fwrite($file, $binary);
    fclose($file);

    $postTitle = $_REQUEST["postTitle"];
    $postCategoryName = Functions::sanitizeParameter($_REQUEST["postCategory"]);
    $postContent = Functions::sanitizeParameter($_REQUEST["postContent"]);
    $postDate = date('Y-m-d H:i:s');

    $result = $validator->arePostDetailsValid($postTitle, $postContent);
    if ($result === true) {

        $serverImageLocation = "http://sgb967.poseidon.salford.ac.uk/cms/" . $fileLocation;
        $dbManager->uploadPost($_REQUEST['userID'],
            $postTitle, $postContent, $postCategoryName, $postDate, $serverImageLocation);
        $postUploaded = $dbManager->fetchLastUserPost($_REQUEST['userID']);
        echo json_encode($postUploaded);
    } else {
        $responseObject->warningMessage = $result;
        echo json_encode($responseObject);
    }

} else {
    $responseObject->errorMessage = "Api key not provided or you tried too many requests in a given time";
    echo json_encode($responseObject);
}
?>