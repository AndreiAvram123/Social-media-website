<?php


class Friend extends User implements JsonSerializable
{

    public function jsonSerialize()
    {

        return
            [
                'userID' => $this->getUserId(),
                'username' => $this->getUsername(),
                'profilePicture' => $this->getProfilePicture(),
                'lastMessage' =>$this->getLastMessage(),
            ];
    }
}