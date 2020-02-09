<?php
require_once("Data/ChatDatabase.php");
$defaultNoResultsMessage = "No results";
$chatDatabase = ChatDatabase::getInstance();

if (isset($_REQUEST["messageContent"]) &&
    isset($_REQUEST["receiverId"]) && isset($_REQUEST["currentUserId"])) {
    $messageDate = time() * 1000;
    $chatDatabase->insertNewMessage($_REQUEST["messageContent"],
        $messageDate, $_REQUEST["currentUserId"], $_REQUEST["receiverId"]);

}


// GET REQUESTS
if (isset($_REQUEST["requestName"])) {
    if (isset($_REQUEST["requestName"]) && $_REQUEST["requestName"] === "fetchMessages") {
        $user1Id = $_REQUEST["user1Id"];
        $user2Id = $_REQUEST["user2Id"];
        $messages = $chatDatabase->getAllMessagesWithUser($user1Id, $user2Id);
        if (sizeof($messages) > 0) {
            echo json_encode($messages);
        } else {
            echo $defaultNoResultsMessage;
        }
    }

    if (isset($_REQUEST["requestName"]) && $_REQUEST["requestName"] === "fetchNewMessages") {
        $receiverId = $_REQUEST["receiverId"];
        $currentUserId = $_REQUEST["currentUserId"];
        $lastMessageDate = $_REQUEST["lastMessageId"];
        $messages = $chatDatabase->getNewMessages($lastMessageDate, $currentUserId, $receiverId);
        if (sizeof($messages) > 0) {
            echo json_encode($messages);
        } else {
            echo $defaultNoResultsMessage;
        }

    }

    if (isset($_REQUEST["requestName"]) && $_REQUEST["requestName"] === "fetchChatId") {
        $user1Id = $_REQUEST["user1Id"];
        $user2Id = $_REQUEST["user2Id"];
        $chatId = $chatDatabase->fetchChatId($user1Id, $user2Id);
        if ($chatId == null) {
            $chatDatabase->createChat($user1Id, $user2Id);
            $chatId = $chatDatabase->fetchChatId($user1Id, $user2Id);
            $chatDatabase->createChatLiveFunctions($chatId, $user1Id, $user2Id);
            //fetch the chat id again
        }
        echo $chatId;

    }

    if ($_REQUEST["requestName"] === "markTyping") {
        $chatDatabase->setUserIsTyping($_REQUEST["chatId"], $_REQUEST["userId"],$_REQUEST["isTyping"]);
    }
    if ($_REQUEST["requestName"] === "checkUserIsTyping") {

        echo $chatDatabase->checkUserIsTyping($_REQUEST["chatId"],$_REQUEST["userId"]);
    }

}

?>