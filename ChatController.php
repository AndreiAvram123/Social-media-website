<?php
require_once("Data/ChatDatabase.php");
require_once("Api/ApiKeyManager.php");
require_once("utilities/Functions.php");

$responseObject = new stdClass();
$chatDatabase = ChatDatabase::getInstance();
$requestAccepted = false;
$apiManager = ApiKeyManager::getInstance();

/**
 * Check weather the request url contains an api key
 * if not ,do not process the request further
 */
if (isset($_REQUEST['apiKey'])) {
    $requestAccepted = $apiManager->isRequestAccepted($_REQUEST['apiKey'], $_SERVER['REMOTE_ADDR']);
}

if ($requestAccepted == true && isset($_REQUEST['requestName'])) {

    if ($_REQUEST["requestName"] == "sendMessage") {

        if (isset($_REQUEST['messageContent']) &&
            isset($_REQUEST['currentUserId']) && isset($_REQUEST['receiverId'])) {
            $messageDate = time() * 1000;
            $sanitizedMessageContent = htmlentities($_REQUEST['messageContent']);
            $chatDatabase->insertNewMessage($sanitizedMessageContent,
                $messageDate,
                Functions::sanitizeParameter($_REQUEST['currentUserId']),
                Functions::sanitizeParameter($_REQUEST['receiverId']));

            $lastMessage = $chatDatabase->fetchLastMessage($_REQUEST['currentUserId'], $_REQUEST['receiverId']);
            echo json_encode($lastMessage);
        }
    }

// GET REQUESTS
    if ($_REQUEST["requestName"] === "fetchOldMessages") {
        $receiverId = Functions::sanitizeParameter($_REQUEST["receiverId"]);
        $currentUserId = Functions::sanitizeParameter($_REQUEST["currentUserId"]);
        $offset = Functions::sanitizeParameter($_REQUEST["offset"]);
        $oldMessages = $chatDatabase->fetchOldMessages($receiverId, $currentUserId, $offset);
        echo json_encode($oldMessages);

    }

    if ($_REQUEST["requestName"] === "fetchNewMessages") {
        $receiverId = Functions::sanitizeParameter($_REQUEST["receiverId"]);
        $currentUserId = Functions::sanitizeParameter($_REQUEST["currentUserId"]);
        $lastMessageID = Functions::sanitizeParameter($_REQUEST["lastMessageId"]);
        $messages = $chatDatabase->getNewMessages($lastMessageID, $currentUserId, $receiverId);
        echo json_encode($messages);
    }

    if ($_REQUEST["requestName"] === "UploadImage") {
        if (isset($_FILES)) {
            $imagePath = $chatDatabase->uploadImageToServer($_FILES["files"]["name"][0], $_FILES["files"]["tmp_name"][0], "images/chatImages/");
            $messageDate = time() * 1000;
            $chatDatabase->insertImageMessage($imagePath,
                $messageDate, $_REQUEST["currentUserId"], $_REQUEST["receiverId"]);

            $lastMessage = $chatDatabase->fetchLastMessage($_REQUEST['currentUserId'], $_REQUEST['receiverId']);

            echo json_encode($lastMessage);
        }

    }

    if ($_REQUEST["requestName"] === "fetchChatId") {
        $user1Id = Functions::sanitizeParameter($_REQUEST["user1Id"]);
        $user2Id = Functions::sanitizeParameter($_REQUEST["user2Id"]);
        $chatId = Functions::sanitizeParameter($chatDatabase->fetchChatId($user1Id, $user2Id));

        if ($chatId == null) {
            $chatDatabase->createChat($user1Id, $user2Id);
            $chatId = $chatDatabase->fetchChatId($user1Id, $user2Id);
            $chatDatabase->createChatLiveFunctions($chatId, $user1Id, $user2Id);
        }
        echo $chatId;
    }

    if ($_REQUEST["requestName"] === "markTyping") {
        $chatDatabase->setUserIsTyping($_REQUEST["chatId"], $_REQUEST["userId"], $_REQUEST["isTyping"]);
    }
    if ($_REQUEST["requestName"] === "checkUser2IsTyping") {
        $responseObject->userIsTyping = $chatDatabase->checkUserIsTyping($_REQUEST["chatId"], $_REQUEST["userId"]);
        echo json_encode($responseObject);
    }

} else {
    $responseObject->errorMessage = "Api key invalid or you tried too many requests within a period of time";
    echo json_encode($responseObject);
}

?>