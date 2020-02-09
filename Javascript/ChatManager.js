//check the database every third of  a second
let timeIntervalCheck = 300;
let timeIntervalCheckTyping = 700;
let intervalCheck;
let userIsTypingCheck;
let currentUserIsTypingCheck;
let lastMessageId;
let sessionUserId;
let chatWindowInitialized = false;
let chatId;
let lastKeyPressedTime;
let shouldFetchNewMessages;
let userIsTypingHint;

function checkUserIsTyping() {
    let url = "ChatController.php?requestName=checkUserIsTyping";
    url += "&userId=" + sessionUserId;
    url += "&chatId=" + chatId;
    getXmlHttpGetRequest(url).onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            console.log(this.responseText);
            if (this.responseText == 1) {
                displayUserTypingHint(true);
            } else {
                displayUserTypingHint(false)
            }
        }

    };
}

function displayUserTypingHint(isTyping) {
    if (isTyping === true) {
        userIsTypingHint.style.display = "block";
    } else {
        userIsTypingHint.style.display = "none";
    }
}

function fetchNewMessages(receiverId, container) {
    let url = "ChatController.php?requestName=fetchNewMessages";
    url += "&lastMessageId=" + lastMessageId;
    url += "&currentUserId=" + sessionUserId;
    url += "&receiverId=" + receiverId;
    if (lastMessageId != null && shouldFetchNewMessages === true) {
        shouldFetchNewMessages = false;
        getXmlHttpGetRequest(url).onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                if (this.responseText !== "No results") {
                    processMessages(this.responseText, container);
                }
                shouldFetchNewMessages = true;
            }
        };
    }
}

function sendMessage(receiverId) {
    let messageField = document.getElementById("messageField");
    let message = messageField.value.trim();
    if (message !== "") {
        let dataToSend = "messageContent=" + message;
        dataToSend += "&receiverId=" + receiverId;
        dataToSend += "&currentUserId=" + sessionUserId;
        getXmlHttpPostRequest(dataToSend);
        messageField.value = "";
    }
}

function getXmlHttpPostRequest(dataToSend) {
    let xhttp = new XMLHttpRequest();
    xhttp.open("POST", "ChatController.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(dataToSend);
    return xhttp;
}

function getXmlHttpGetRequest(url) {
    let xhttp = new XMLHttpRequest();
    xhttp.open("GET", url, true);
    xhttp.send();
    return xhttp;
}


function startChat(currentUserId, receiverId, username) {
    if (chatWindowInitialized === false) {
        sessionUserId = currentUserId;
        document.body.innerHTML += '<div class="message-window" >\n' +
            '<i class="fas fa-times float-right" onclick="removeChat(this.parentNode)"></i>' +
            '    <div class="message-header" onclick="toggleElement(document.getElementById(' + '\'' + 'message-window-body' + '\')' + ')">' +
            '        <p class="text-center" style="color: white">' + username + '</p>\n' +
            '    </div>\n' +
            '    <div id="message-window-body">\n' +
            '        <div class ="message-container">\n' +
            '\n' +
            '        </div>\n' +
            '<p id="user-is-typing-hint">User is typing</p>' +
            '        <div class="message-footer">\n' +
            '            <textarea type="text" id="messageField"></textarea>\n' +
            '            <button onclick="sendMessage(' + '\'' + receiverId + '\'' + ')"><i\n' +
            '                    class="far fa-paper-plane"></i></button>\n' +
            '        </div>\n' +
            '    </div>\n'
        '</div>';
        userIsTypingHint = document.getElementById("user-is-typing-hint");
        userIsTypingHint.style.display = "none";
        (document.getElementById("messageField")).addEventListener('keyup', (event) => {
            if (event.key === "Enter") {
                sendMessage(receiverId);
            } else {
                if (event.key !== "Backspace") {
                    markCurrentUserAsTyping(true);
                }
            }


        });
        fetchChatMessages(receiverId, document.getElementsByClassName("message-container")[0]);
    }

}

function markCurrentUserAsTyping($isTyping) {
    lastKeyPressedTime = new Date().getTime();
    let dataToSend = "ChatController.php?requestName=markTyping";
    dataToSend += "&userId=" + sessionUserId;
    dataToSend += "&chatId=" + chatId;
    dataToSend += "&isTyping=" + $isTyping;
    getXmlHttpGetRequest(dataToSend);
}

function removeChat(chat) {
    document.body.removeChild(chat);
    chatWindowInitialized = false;
}

function shouldPlayNotificationSound() {
    if (!document.hasFocus()) {
        return true;
    }

    return document.activeElement.id !== "messageField" && chatWindowInitialized === true;


}

function processMessages(data, container) {
    function playNotificationSound() {
        let audioPlayer = document.getElementById("notificationAudio");
        audioPlayer.play();
    }

    if (shouldPlayNotificationSound()) {
        playNotificationSound();
    }
    let jsonDataArray = JSON.parse(data);
    jsonDataArray.forEach(elementData => {
        addMessageToChat(elementData, container);
    });
    //set the last message id
    lastMessageId = jsonDataArray[jsonDataArray.length - 1].messageId;
    chatWindowInitialized = true;
}

function addMessageToChat(messageJson, container) {
    let messageView = document.createElement("p");
    messageView.innerText = messageJson.messageContent;
    if (messageJson.senderId === sessionUserId) {
        messageView.style.textAlign = "right";
    }
    container.appendChild(messageView);
    scrollToLastMessage(container);

}

function scrollToLastMessage(container) {
    container.scrollTop = container.lastChild.offsetTop;
}

function checkCurrentUserTyping() {
    let currentTime = new Date().getTime();
    if (lastKeyPressedTime + 20 < currentTime) {
        markCurrentUserAsTyping(false);
    }
}

function fetchChatMessages(user2Id, container) {

    function getChatId() {
        let url = "ChatController.php?requestName=fetchChatId";
        url += "&user1Id=" + sessionUserId;
        url += "&user2Id=" + user2Id;
        getXmlHttpGetRequest(url).onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                chatId = this.responseText;
            }
        }
    }

    getChatId();
    let url = "ChatController.php?requestName=fetchMessages";
    url += "&user1Id=" + sessionUserId;
    url += "&user2Id=" + user2Id;

    function initializeAsyncFunctions() {
        intervalCheck = setInterval(fetchNewMessages, timeIntervalCheck, user2Id, container);
        userIsTypingCheck = setInterval(checkUserIsTyping, timeIntervalCheckTyping);
        currentUserIsTypingCheck = setInterval(checkCurrentUserTyping, timeIntervalCheckTyping);

    }

    getXmlHttpGetRequest(url).onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            if (this.responseText.trim() !== "No results") {
                processMessages(this.responseText, container, sessionUserId);
                initializeAsyncFunctions();

            }
            shouldFetchNewMessages = true;
        }
    };
}

function toggleElement(element) {
    if (element.style.display !== "none") {
        element.style.display = "none";
    } else {
        element.style.display = "block";
    }
}


















