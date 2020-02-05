<?php

class FriendRequest
{
    private $requestId;
    private $senderId;
    private $receiverId;

    public function __construct($row)
    {
        $this->senderId = $row["sender_id"];
        $this->receiverId = $row["receiver_id"];
        $this->receiverId = $row["request_id"];
    }


    public function getSenderId()
    {
        return $this->senderId;
    }


    public function getReceiverId()
    {
        return $this->receiverId;
    }


    public function getRequestId()
    {
        return $this->requestId;
    }


}