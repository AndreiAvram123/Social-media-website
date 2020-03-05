<?php
require_once("Data/FriendsDatabase.php");
require_once("Data/DataManager.php");

if (isset($_REQUEST["query"])) {
    $query = htmlentities($_REQUEST["query"]);
    $friendDb = FriendsDatabase::getInstance();
    $query = $_REQUEST["query"];
    $suggestions = $friendDb->getAllFriendsSuggestionsForQuery($query);
    echo json_encode($suggestions);
}
if (isset($_REQUEST["postsSearchQuery"])) {
    $query = htmlentities($_REQUEST["postsSearchQuery"]);
    $postCategory = null;
    $sortDate = null;
    if (isset($_REQUEST['sortDate'])) {
        $sortDate = $_REQUEST['sortDate'];
    }
    if (isset($_REQUEST['category'])) {
        $postCategory = $_REQUEST['category'];
    }
    $dbManager = DataManager::getInstance();

    $fetchedSuggestions = $dbManager->fetchSearchSuggestions($query, $sortDate, $postCategory);
    echo json_encode($fetchedSuggestions);
}
?>
