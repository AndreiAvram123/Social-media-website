//check the database every third of  a second
let timeIntervalCheck = 300;
let timeIntervalCheckTyping = 700;
let intervalCheck;
let userIsTypingCheck;
let currentUserIsTypingCheck;
let lastMessageId = null;
let sessionUserId;
let chatWindowInitialized = false;
let chatId;
let lastKeyPressedTime;
let shouldFetchNewMessages = true;
let defaultResponseNoData = "No results";
let chatWindow;

class ChatWindow {
    constructor(username, receiverId) {
        let domParser = new DOMParser();
        let chatString = '<div class="message-window" >\n' +
            '<i class="fas fa-times float-right"></i>' +
            '    <div class="message-header" onclick="toggleElement(document.getElementById(' + '\'' + 'message-window-body' + '\')' + ')">' +
            '        <p class="text-center" style="color: white">' + username + '</p>\n' +
            '    </div>\n' +
            '    <div id="message-window-body">\n' +
            '        <div class ="message-container container">\n' +
            '\n' +
            '        </div>\n' +
            '<p id="user-is-typing-hint" style="display: none">User is typing</p>' +
            '        <div class="message-footer">\n' +
            '            <textarea type="text" id="messageField"></textarea>\n' +
            '<button class="select-image-icon" ><i class="fas fa-images"></i></button>' +
            '            <button onclick="sendMessage(' + '\'' + receiverId + '\'' + ')"><i\n' +
            '                    class="far fa-paper-plane"></i></button>\n' +
            '        </div>\n' +
            ' <input type="file"  name="files[]"  style="display: none"' +
            '    </div>\n' +
            '</div>';
        let domElement = domParser.parseFromString(chatString, "text/html");
        this.chatWindow = domElement.documentElement;
        this.userIsTypingHint = domElement.getElementById("user-is-typing-hint");

        domElement.getElementsByTagName("i")[0].addEventListener('click', event => removeChat(this.chatWindow));
        let imageSelector = domElement.getElementsByName("files[]")[0];
        let imageSelectIcon = domElement.getElementsByClassName("select-image-icon")[0];
        imageSelectIcon.addEventListener('click', event => imageSelector.click());
        imageSelector.addEventListener('change', event => {
            uploadImage(receiverId);
        });
        document.body.append(this.chatWindow);
    }

    playNotificationSound() {
        let audioPlayer = document.getElementById("notificationAudio");
        audioPlayer.play();
    }

    addMessagesToChat(messagesJson) {
        let messageContainer = this.chatWindow.getElementsByClassName("message-container")[0];
        let playNotification = false;
        messagesJson.forEach(messageJson => {
            let messageView;
            if (messageJson.messageImage == null) {
                messageView = document.createElement("span");
                messageView.style.display = "block";
                messageView.innerText = messageJson.messageContent;
            } else {
                messageView = document.createElement("div");
                messageView.className = "float-right";
                let messageImage = document.createElement("img");
                messageImage.src = messageJson.messageImage;
                messageImage.className = "message-image";
                messageView.appendChild(messageImage);
            }
            if (messageJson.senderId === sessionUserId) {
                messageView.style.textAlign = "right";
            } else {
                playNotification = true;
            }

            messageContainer.appendChild(messageView);
        });
        scrollToLastMessage(messageContainer);
        if (playNotification && shouldPlayNotificationSound()) {
            chatWindow.playNotificationSound();
        }

    }

    displayUserTypingHint(isTyping) {
        if (isTyping === true) {
            this.userIsTypingHint.style.display = "block";
        } else {
            this.userIsTypingHint.style.display = "none";
        }
    }

    stopAsyncFunctions() {
        clearInterval(intervalCheck);
        clearInterval(userIsTypingCheck);
        clearInterval(currentUserIsTypingCheck);
    }

}

function removeChat(chat) {
    chatWindowInitialized = false;
    lastMessageId = null;
    chatWindow.stopAsyncFunctions();
    document.body.removeChild(chat);

}


function uploadImage(receiverId) {
    let url = "ChatController.php?requestName=UploadImage";
    let formData = new FormData();
    const files = document.querySelector('[type=file]').files;


    formData.append('files[]', files[0]);
    formData.append("receiverId", receiverId);
    formData.append("currentUserId", sessionUserId);

    fetch(url, {
        method: 'POST',
        body: formData,
    }).then(function (response) {
        return response.text()
    }).then(function (data) {
        console.log(data);
    })
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


function checkUserIsTyping() {
    let url = "ChatController.php?requestName=checkUserIsTyping";
    url += "&userId=" + sessionUserId;
    url += "&chatId=" + chatId;
    getXmlHttpGetRequest(url).onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            if (this.responseText == 1) {
                chatWindow.displayUserTypingHint(true);
            } else {
                chatWindow.displayUserTypingHint(false)
            }
        }

    };
}


function fetchNewMessages(receiverId, container) {
    let url = "ChatController.php?requestName=fetchNewMessages";
    url += "&lastMessageId=" + lastMessageId;
    url += "&currentUserId=" + sessionUserId;
    url += "&receiverId=" + receiverId;
    if (shouldFetchNewMessages === true) {
        shouldFetchNewMessages = false;
        getXmlHttpGetRequest(url).onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                if (this.responseText !== defaultResponseNoData) {
                    processMessages(this.responseText, container);
                }
                shouldFetchNewMessages = true;
            }
        };
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

    function resetDatabase() {
        markCurrentUserAsTyping(false);
    }

    window.onbeforeunload = resetDatabase;

    if (chatWindowInitialized === false) {
        sessionUserId = currentUserId;
        chatWindow = new ChatWindow(username, receiverId);
        chatWindowInitialized = true;
        (document.getElementById("messageField")).addEventListener('keyup', (event) => {
            if (event.key === "Enter") {
                sendMessage(receiverId);
            } else {
                if (event.key !== "Backspace") {
                    markCurrentUserAsTyping(true);
                }
            }


        });

        initializeChatFunctions(receiverId, document.getElementsByClassName("message-container")[0]);
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


function shouldPlayNotificationSound() {
    if (!document.hasFocus()) {
        return true;
    }
    return document.activeElement.id !== "messageField" && chatWindowInitialized === true;

}

function processMessages(data) {
    let jsonDataArray = JSON.parse(data);
    chatWindow.addMessagesToChat(jsonDataArray);
    //set the last message id
    lastMessageId = jsonDataArray[jsonDataArray.length - 1].messageId;
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

function initializeChatFunctions(user2Id, container) {

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

    function initializeAsyncFunctions() {
        intervalCheck = setInterval(fetchNewMessages, timeIntervalCheck, user2Id, container);
        userIsTypingCheck = setInterval(checkUserIsTyping, timeIntervalCheckTyping);
        currentUserIsTypingCheck = setInterval(checkCurrentUserTyping, timeIntervalCheckTyping);

    }

    initializeAsyncFunctions();
}

function sendImage() {
    let formData = new FormData();

}

function toggleElement(element) {
    if (element.style.display !== "none") {
        element.style.display = "none";
    } else {
        element.style.display = "block";
    }
}


















