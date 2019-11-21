<?php
require_once "Data/DatabaseHandler.php";
session_start();
$view = new stdClass();
$view->isUserLoggedIn = isset($_SESSION['user_id']);
$view->pageTitle = "Search result";
$dbHandler = new DatabaseHandler();

if(isset($_POST['search-button'])){
   $view -> searchResults =$dbHandler->getSearchResult($_POST['search-text']);
}

include "Views/Search.phtml";
?>