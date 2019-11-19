<?php
 require_once "Data/DatabaseHandler.php";
 require_once "Data/Post.php";

 class Dataset{
     protected $_dbHandle;

     public function __construct()
     {
         $this->_dbHandle = DatabaseHandler::getInstance();
     }


    public function getMostRecentPosts(){
         $posts = [];
         foreach ($this->_dbHandle->fetchMostRecentPosts() as $row){
            $posts[] = new Post($row);
         }
         return $posts;
     }

 }
?>