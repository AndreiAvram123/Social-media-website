<?php
require_once("Restful/RestfulDatabase.php");
require_once ("Data/DataManager.php");


$dbHandler = DataManager::getInstance();
if (isset($_GET['recentPosts'])) {
   echo json_encode($dbHandler->getPosts(1));
}

?>