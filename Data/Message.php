<?php

class Message implements JsonSerializable
{
  private  $messageId;
  private  $messageContent;
  private  $messageDate;
  private  $senderId;
  private  $receiverId;
  private  $messageImage;

  public function __construct($row)
  {
    $this->messageId = $row["message_id"];
    $this->messageContent = $row["message_content"];
    $this->messageDate = (int) $row["message_date"];
    $this->senderId = $row["sender_id"];
    $this->receiverId = $row["receiver_id"];
    $this->messageImage = $row["message_image"];
  }


    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return
            [
                'messageId' => $this->messageId,
                'messageContent' => $this->messageContent,
                'messageDate' => $this->messageDate,
                'senderId' => $this->senderId,
                'receiverId' => $this->receiverId,
                'messageImage' => $this->messageImage,
            ];
    }
}