<?php
require_once("Data/FriendsDatabase.php");
require_once("Data/DataManager.php");
require_once("Api/ApiKeyManager.php");
include_once("utilities/Functions.php");

$apiManager = ApiKeyManager::getInstance();


$responseObject = new stdClass();
$requestAccepted = false;

/**
 * Check weather the request url contains an api key
 * if not ,do not process the request further
 */
if (isset($_REQUEST['apiKey'])) {
    $requestAccepted = $apiManager->isRequestAccepted($_REQUEST['apiKey'], $_SERVER['REMOTE_ADDR']);

}


if ($requestAccepted == true) {
    if (isset($_REQUEST["query"])) {
        $query = Functions::sanitizeParameter($_REQUEST["query"]);
        $suggestions = [];
        if ($query !== "") {
            $friendDb = FriendsDatabase::getInstance();
            $suggestions = $friendDb->getAllFriendsSuggestionsForQuery($query);
        }
        echo json_encode($suggestions);
    }


    if (isset($_REQUEST["postsSearchQuery"])) {
        $query = Functions::sanitizeParameter($_REQUEST["postsSearchQuery"]);
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
                    $fetchedSuggestions[$i]->setPostID(Functions::encodeWithSha512($fetchedSuggestions[$i]->getPostID()));
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
