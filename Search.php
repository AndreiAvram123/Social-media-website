<?php
require_once "Data/DataManager.php";
include_once "utilities/Functions.php";
require_once("Api/ApiKeyManager.php");

session_start();
$view = new stdClass();
$view->pageTitle = "Search results";
$dbHandler = DataManager::getInstance();
$view->categories = $dbHandler->getAllCategories();

$apiKeyManager = ApiKeyManager::getInstance();
$view->apiKey = $apiKeyManager->obtainApiKey($_SERVER['REMOTE_ADDR']);

if (isset($_GET['search-button'])) {
    //make sure that the user has not inserted any code in the search box
    $searchQuery = Functions::sanitizeParameter($_GET['search-posts-field']);
    //get query filters
    $category = Functions::sanitizeParameter($_GET['postCategoryFilter']);
    $maxNumberOfResults = Functions::sanitizeParameter($_GET['postMaxResults']);
    $order = Functions::sanitizeParameter($_GET['postOrder']);
    $view->searchResults = $dbHandler->getSearchResult($searchQuery, $category, $order, $maxNumberOfResults);
}

include "Views/Search.phtml";
?>