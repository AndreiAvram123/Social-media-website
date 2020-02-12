<?php


class ChatLiveFunctionsModel
{
    public $chatID;
    public $userID;
    public $userIsTyping;
    public $userSentNewMessages;

    /**
     * ChatLiveFunctionsModel constructor.
     * @param $row
     */
    public function __construct($row)
    {
        $this->chatID = $row['chat_id'];
        $this->userID = $row['user_id'];
        $this->userIsTyping = $row['user_is_typing'];
        $this->userSentNewMessages = $row['user_sent_new_messages'];
    }

}