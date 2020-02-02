<?php

class FriendRequest
{
   private $senderId;
   private $receiverId;

   public function __construct($row)
   {
       $this->senderId = $row["sender_id"];
       $this->receiverId = $row["receiver_id"];
   }


    public function getSenderId()
    {
        return $this->senderId;
    }


    public function getReceiverId()
    {
        return $this->receiverId;
    }

}