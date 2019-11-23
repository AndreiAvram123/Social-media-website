<?php
require_once "Data/DataManager.php";
session_start();
$view = new stdClass();
$view->isUserLoggedIn = isset($_SESSION['user_id']);
$view->pageTitle = "Search result";
$dbHandler = new DataManager();

if (isset($_POST['search-button'])) {
    $searchQuery = htmlentities($_POST['search-text']);
    $view->searchResults = $dbHandler->getSearchResult($searchQuery);
}

include "Views/Search.phtml";
?>