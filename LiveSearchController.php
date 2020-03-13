<?php
require_once("Data/FriendsDatabase.php");
require_once("Data/DataManager.php");
require_once("Api/ApiKeyManager.php");
include_once("utilities/CommonFunctions.php");



$apiManager = ApiKeyManager::getInstance();
$responseObject = new stdClass();
$apiKey = null;

if (isset($_REQUEST['apiKey'])) {
    $apiKey = $apiManager->fetchApiKey($_SERVER['REMOTE_ADDR']);
}

if ($apiKey != null) {

    if (isset($_REQUEST["query"])) {
        $query = CommonFunctions::getSanitizedQuery($_REQUEST["query"]);
        $suggestions = [];
        if ($query !== "") {
            $friendDb = FriendsDatabase::getInstance();
            $suggestions = $friendDb->getAllFriendsSuggestionsForQuery($query);
        }
        echo json_encode($suggestions);
    }


    if (isset($_REQUEST["postsSearchQuery"])) {
        $query = CommonFunctions::getSanitizedQuery($_REQUEST["postsSearchQuery"]);
        $postCategory = null;
        $sortDate = null;
        if (isset($_REQUEST['sortDate'])) {
            $sortDate = $_REQUEST['sortDate'];
        }
        if (isset($_REQUEST['category'])) {
            $postCategory = $_REQUEST['category'];
        }

        $fetchedSuggestions = [];
        if ($query !== "") {
            $dbManager = DataManager::getInstance();
            $fetchedSuggestions = $dbManager->fetchSearchSuggestions($query, $sortDate, $postCategory);
        }
        echo json_encode($fetchedSuggestions);
    }


} else {
    $responseObject->errorMessage = "Api key not provided or invalid";
    echo json_encode($responseObject);
}
?>
