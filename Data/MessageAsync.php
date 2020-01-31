<?php
$path = $_SERVER['DOCUMENT_ROOT']."/myCms/Data/ChatManager.php";
require_once($path);
$dbInstance = ChatManager::getInstance();

if(isset($_POST["messageContent"])){
    $messageDate = date('Y-m-d H:i:s');
   $dbInstance->postMessage($_POST["messageContent"],$messageDate);
   echo $messageDate;
 }

?>