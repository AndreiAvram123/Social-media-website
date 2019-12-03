<?php
session_start();
require_once "Data/SessionManager.php";
require_once "Data/DataManager.php";
$view = new stdClass();
$view->pageTitle = "Home";
$dbHandle = DataManager::getInstance();
$view -> numberOfPages = $dbHandle->getNumberOfPages();

if (isset($_POST['signOutButton'])) {
    SessionManager::getInstance()->signUserOut();
    $view->redirectHome = true;
}
$postsOffset =0;
//handle pagination
for ($i=1;$i<= $view ->numberOfPages;$i++){
    if(isset($_POST[md5($i)])){
        //check if the user has not changed the value
        //in the inspector
        if($i>=1 && $i<=$view->numberOfPages){
            //as an example ,for the first
            //page we will have an offset of 0 posts
          $postsOffset = ($i-1)*DataManager::$postPerPage;
        }
    }
}

$view -> posts = $dbHandle->getPosts($postsOffset);

$view ->isUserLoggedIn = isset($_SESSION['user_id']);

require_once "Views/index.phtml";
?>


