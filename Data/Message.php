<?php


class Message
{
  private $messageId;
  private $messageContent;
  private $messageDate;


  public function __construct($row)
  {
    $this->messageId = $row["message_id"];
    $this->messageContent = $row["message_content"];
    $this->messageDate = $row["message_date"];
  }

    /**
     * @return mixed
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @return mixed
     */
    public function getMessageContent()
    {
        return $this->messageContent;
    }

    /**
     * @return mixed
     */
    public function getMessageDate()
    {
        return $this->messageDate;
    }

}