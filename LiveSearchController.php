<?php
require_once("Data/FriendsDatabase.php");
require_once("Data/DataManager.php");
require_once("Api/ApiKeyManager.php");
include_once("utilities/CommonFunctions.php");

$apiManager = ApiKeyManager::getInstance();


$responseObject = new stdClass();
$requestAccepted = false;

/**
 * Check weather the request url contains an api key
 * if not ,do not process the request further
 */
if (isset($_REQUEST['apiKey'])) {
    //get the entered key
    $apiKeyEntered = CommonFunctions::getSanitizedParameter($_REQUEST['apiKey']);
    //get the api key from the database
    $apiKeyDatabase = $apiManager->fetchApiKey($_SERVER['REMOTE_ADDR']);

    if ($apiKeyDatabase !== null && $apiKeyEntered === $apiKeyDatabase) {
        $requestAccepted = $apiManager->isRequestAccepted($apiKeyEntered);
    }
}



if ($requestAccepted == true) {
    $apiManager->setLastRequestTime(CommonFunctions::getSanitizedParameter($_REQUEST['apiKey']));
    if (isset($_REQUEST["query"])) {
        $query = CommonFunctions::getSanitizedParameter($_REQUEST["query"]);
        $suggestions = [];
        if ($query !== "") {
            $friendDb = FriendsDatabase::getInstance();
            $suggestions = $friendDb->getAllFriendsSuggestionsForQuery($query);
        }
        echo json_encode($suggestions);
    }


    if (isset($_REQUEST["postsSearchQuery"])) {
        $query = CommonFunctions::getSanitizedParameter($_REQUEST["postsSearchQuery"]);
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
            if (isset($_REQUEST['encrypted'])) {
                for ($i = 0; $i < sizeof($fetchedSuggestions); $i++) {
                    $fetchedSuggestions[$i]->setPostID(CommonFunctions::encodeWithSha512($fetchedSuggestions[$i]->getPostID()));
                }
            }

        }
        echo json_encode($fetchedSuggestions);
    }


} else {
    $responseObject->errorMessage = "Api key not provided or you tried too many requests in a given time";
    echo json_encode($responseObject);
}
?>
