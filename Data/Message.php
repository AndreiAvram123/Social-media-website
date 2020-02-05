<?php


class Message
{
  public $messageId;
  public $messageContent;
  public $messageDate;
  public $senderId;
  public $receiverId;

  public function __construct($row)
  {
    $this->messageId = $row["message_id"];
    $this->messageContent = $row["message_content"];
    $this->messageDate = $row["message_date"];
    $this->senderId = $row["sender_id"];
    $this->receiverId = $row["receiver_id"];
  }

}