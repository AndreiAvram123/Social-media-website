<?php
require_once "Data/DataManager.php";
session_start();
$view = new stdClass();
$view->isUserLoggedIn = isset($_SESSION['user_id']);
$view->pageTitle = "Search result";
$dbHandler = new DataManager();

if(isset($_POST['search-button'])){
   $view -> searchResults =$dbHandler->getSearchResult($_POST['search-text']);
}

include "Views/Search.phtml";
?>