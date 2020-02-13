<?php
require_once("Data/FriendsDatabase.php");
require_once("Data/DataManager.php");
$defaultNoResultsMessage = "No results";

if (isset($_REQUEST["query"])) {
    $query = htmlentities($_REQUEST["query"]);
    $friendDb = FriendsDatabase::getInstance();
    $query = $_REQUEST["query"];
    $suggestions = $friendDb->getAllFriendsSuggestionsForQuery($query);;
    if (sizeof($suggestions) > 0) {
        echo json_encode($suggestions);
    } else {
        echo $defaultNoResultsMessage;
    }
}
if (isset($_REQUEST["postsSearchQuery"])) {
    $query = htmlentities($_REQUEST["postsSearchQuery"]);
    $sortDate = $_REQUEST['sortDate'];
    $postCategory = $_REQUEST['category'];
    $dbManager = DataManager::getInstance();

    $fetchedSuggestions = $dbManager->fetchSearchSuggestions($query, $sortDate, $postCategory);
    if ($fetchedSuggestions !== "") {
        echo $fetchedSuggestions;
    } else {
        echo $defaultNoResultsMessage;
    }
}
?>
