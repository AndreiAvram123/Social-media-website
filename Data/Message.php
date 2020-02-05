<?php


class Message
{
  public $messageId;
  public $messageContent;
  public $messageDate;
  public $sender_id;
  public $receiver_id;

  public function __construct($row)
  {
    $this->messageId = $row["message_id"];
    $this->messageContent = $row["message_content"];
    $this->messageDate = $row["message_date"];
    $this->sender_id = $row["sender_id"];
    $this->receiver_id = $row["receiver_id"];
  }

}