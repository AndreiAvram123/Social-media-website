<?php
require_once "Data/DataManager.php";
session_start();
$view = new stdClass();
$view->isUserLoggedIn = isset($_SESSION['user_id']);
$view->pageTitle = "Search results";
$dbHandler = new DataManager();

if (isset($_POST['search-button'])) {
    //make sure that the user has not inserted any code in the search box
    $searchQuery = htmlentities($_POST['search-text']);
    //get query filters
    $category = htmlentities($_POST['postCategoryFilter']);
    $maxNumberOfResults = htmlentities($_POST['postMaxResults']);
    $order = htmlentities($_POST['postOrder']);
    $view->searchResults = $dbHandler->getSearchResult($searchQuery,$category,$order,$maxNumberOfResults);
}

include "Views/Search.phtml";
?>