<?php
require_once("Data/ChatDatabase.php");
include "Data/Constants.php";

$defaultNoResultsMessage = "No results";
$responseObject = new stdClass();
$chatDatabase = ChatDatabase::getInstance();

if (isset($_REQUEST["requestName"]) && $_REQUEST["requestName"] == "sendMessage") {

    if (isset($_REQUEST['messageContent']) &&
        isset($_REQUEST['currentUserId']) && isset($_REQUEST['receiverId'])) {
        $messageDate = time() * 1000;
        $chatDatabase->insertNewMessage($_REQUEST['messageContent'],
            $messageDate, $_REQUEST['currentUserId'], $_REQUEST['receiverId']);
        //as a response give the client the last message id in order
        //to eliminate the need of fetching the last message as well
        $responseObject->lastMessageID = $chatDatabase->fetchLastMessageID($_REQUEST['currentUserId'], $_REQUEST['receiverId']);
        $responseObject->lastMessageDate = $messageDate;
        echo json_encode($responseObject);
    }
}

// GET REQUESTS

if ($_REQUEST["requestName"] === "fetchOldMessages") {
    $receiverId = $_REQUEST["receiverId"];
    $currentUserId = $_REQUEST["currentUserId"];
    $offset = $_REQUEST["offset"];
    $oldMessages = $chatDatabase->fetchOldMessages($receiverId, $currentUserId, $offset);
    if (sizeof($oldMessages) > 0) {
        echo json_encode($oldMessages);
    } else {
        $responseObject->responseCode = Constants::$defaultNoDataResponseCode;
        echo json_encode($responseObject);
    }
}

if (isset($_REQUEST["requestName"])) {


    if ($_REQUEST["requestName"] === "fetchNewMessages") {
        $receiverId = $_REQUEST["receiverId"];
        $currentUserId = $_REQUEST["currentUserId"];
        $lastMessageID = $_REQUEST["lastMessageId"];
        $messages = $chatDatabase->getNewMessages($lastMessageID, $currentUserId, $receiverId);
        echo json_encode($messages);

    }


    if ($_REQUEST["requestName"] === "fetchChatId") {
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

    if ($_REQUEST["requestName"] === "UploadImage") {
        if (isset($_FILES)) {
            $imagePath = $chatDatabase->uploadImageToServer($_FILES["files"]["name"][0], $_FILES["files"]["tmp_name"][0], "images/chatImages/");
            $messageDate = time() * 1000;
            $chatDatabase->insertImageMessage($imagePath,
                $messageDate, $_REQUEST["currentUserId"], $_REQUEST["receiverId"]);

        }

    }

    if ($_REQUEST["requestName"] === "markTyping") {
        $chatDatabase->setUserIsTyping($_REQUEST["chatId"], $_REQUEST["userId"], $_REQUEST["isTyping"]);
    }
    if ($_REQUEST["requestName"] === "checkUserIsTyping") {
        echo $chatDatabase->checkUserIsTyping($_REQUEST["chatId"], $_REQUEST["userId"]);
    }

}

?>