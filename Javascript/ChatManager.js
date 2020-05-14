//check the database every third of  a second
let timeIntervalCheckMessageCheck = 1500;
let timeIntervalCheckTyping = 2000;
let sessionUserId;
let chatWindow;
let lastMouseMovedTime;
//in milliseconds
let timeBeforeIdleMode = 15000;
let unreadMessagesInterval;
let newMessagesCheckRunning = false;

class ChatWindow {
    constructor(username, receiverId) {
        //use a dom parser to convert html into a document element
        let domParser = new DOMParser();
        //the html element for a chat window
        let chatString = '<div class="message-window" >\n' +
            '<i class="fas fa-times float-right"></i>' +
            '    <div class="message-header" onclick="toggleChatWindow(document.getElementById(' + '\'' + 'message-window-body' + '\')' + ')">' +
            '        <p class="text-center" style="color: white">' + username + '</p>\n' +
            '    </div>\n' +
            '    <div id="message-window-body">\n' +
            '        <div class ="message-container">\n' +
            '\n' +
            '        </div>\n' +
            '<p id="user-is-typing-hint" style="display: none">User is typing</p>' +
            '        <div class="message-footer">\n' +
            '            <textarea type="text" id="messageField"></textarea>\n' +
            '<button class="select-image-icon" ><i class="fas fa-images"></i></button>' +
            '            <button onclick="sendMessage(' + '\'' + receiverId + '\'' + ')"><i\n' +
            '                    class="far fa-paper-plane"></i></button>\n' +
            '        </div>\n' +
            ' <input type="file"  name="chatImage"  style="display: none"' +
            '    </div>\n' +
            '</div>';
        let domElement = domParser.parseFromString(chatString, "text/html");
        this.messageFactory = new MessageFactory();
        this.initializeDefaultParameters();
        this.initializeViews(domElement);
        this.attachListeners(domElement);
        this.receiverID = receiverId;
        document.body.append(this.chatWindow);
    }

    /**
     * This method is used to initialize specific default
     * parameters that are necessary for a chat window
     * to function
     * @param receiverId
     */
    initializeDefaultParameters() {
        this.currentlyDisplayedMessages = 0;
        this.fetchMessagesRequestSent = false;
        this.noMoreOldMessagesToFetch = false;
        this.asyncFunctionsRunning = false;
        this.lastMessageID = -1;
        this.scrollTopFetchOffset = 200;
        this.intervalCheck = undefined;
        this.userIsTypingCheck = undefined;
        this.idleCheck = undefined;
        this.currentUserIsTypingCheck = undefined;
        this.lastKeyPressedTime = new Date().getTime() - 3000;
    }

    addNewMessage(messageJson) {
        let messageView = this.messageFactory.createMessageElement(messageJson);
        this.messageContainer.appendChild(messageView);
        this.notifyNewMessageAdded(messageJson.messageId)
    }

    addOldMessagesToContainer(messages) {
        if (messages.length === 0) {
            this.noMoreOldMessagesToFetch = true;
        } else {
            messages.forEach((message) => {
                let messageView = this.messageFactory.createMessageElement(message);
                if (this.messageContainer.childNodes.length > 0) {
                    this.messageContainer.insertBefore(messageView, this.messageContainer.childNodes[0]);
                } else {
                    this.messageContainer.appendChild(messageView);
                }
                this.currentlyDisplayedMessages++;
            });
        }
    }

    notifyNewMessageAdded(lastMessageID) {
        chatWindow.lastMessageID = lastMessageID;
        chatWindow.scrollToLastFetchedMessage();
    }

    requestFocusOnTextArea() {
        this.messageField.focus();
        this.messageField.select();
    }

    /**
     * This method is used to attach listeners
     * to the chat window
     * @param domElement
     * @param receiverId
     */

    attachListeners(domElement) {
        domElement.getElementsByClassName("fas fa-times")[0].onclick = () => removeChat(this.chatWindow);
        let imageSelector = domElement.getElementsByName("chatImage")[0];
        imageSelector.onchange = () => uploadImage();
        let imageSelectIcon = domElement.getElementsByClassName("select-image-icon")[0];
        imageSelectIcon.onclick = () => imageSelector.click();
        this.messageField = domElement.getElementById("messageField");
        this.messageField.onkeyup = (event) => {
            if (event.key === "Enter") {
                sendMessage();
            } else {
                if (event.key !== "Backspace") {
                    chatWindow.lastKeyPressedTime = new Date().getTime();
                }
            }

        };
        this.messageField.onfocus = () => {
            this.hideNotification();
            markMessagesAsSeen();
        };
        domElement.getElementsByClassName("message-window")[0].onclick = () => this.hideNotification();

        this.attachScrollListener();
    }


    initializeViews(domElement) {
        this.chatWindow = domElement.documentElement;
        this.userIsTypingHint = domElement.getElementById("user-is-typing-hint");
        this.messageContainer = domElement.getElementsByClassName("message-container")[0];
        this.chatHeader = domElement.getElementsByClassName("message-header")[0];
    }

    displayUserTypingHint(isTyping) {
        if (isTyping === true) {
            this.userIsTypingHint.style.display = "block";
        } else {
            this.userIsTypingHint.style.display = "none";
        }
    }


    stopAsyncFunctions() {
        this.asyncFunctionsRunning = false;
        clearInterval(this.intervalCheck);
        clearInterval(this.userIsTypingCheck);
        clearInterval(this.currentUserIsTypingCheck);
    }


    getMessageContainerTopOffset() {
        return this.messageContainer.scrollTop;
    }


    attachScrollListener() {
        this.messageContainer.addEventListener('scroll', () => {
            if (this.getMessageContainerTopOffset() <= this.scrollTopFetchOffset) {
                if (this.fetchMessagesRequestSent !== true && this.noMoreOldMessagesToFetch === false) {
                    fetchOldMessages();

                }
            }

        })
    }


    addNewMessagesToContainer(messagesJson) {
        if (messagesJson.length > 0) {
            messagesJson.forEach((message) => {
                let messageView = this.messageFactory.createMessageElement(message);
                this.messageContainer.appendChild(messageView);
            });

            chatWindow.lastMessageID = messagesJson[messagesJson.length - 1].messageId;
            if (!document.hasFocus()) {
                playNotificationSound();
            }
            this.showNotificationInChat();
            chatWindow.scrollToLastFetchedMessage();
        }

    }

    showNotificationInChat() {
        if (document.activeElement !== chatWindow.messageField) {
            this.chatHeader.style.background = "#ffb465";
        }
    }


    hideNotification() {
        this.chatHeader.style.background = "#007BFF";
    }

    scrollToLastFetchedMessage() {
        this.messageContainer.scrollTop = this.messageContainer.scrollHeight;
    }

    initializeDatabaseCheck() {
        if (this.asyncFunctionsRunning === false) {
            this.asyncFunctionsRunning = true;
            this.intervalCheck = setInterval(fetchNewMessages, timeIntervalCheckMessageCheck, this.receiverID, this.messageContainer);
            this.userIsTypingCheck = setInterval(checkUser2IsTyping, timeIntervalCheckTyping);
            this.currentUserIsTypingCheck = setInterval(checkCurrentUserTyping, timeIntervalCheckTyping);
        }
    }
}

/**
 * Factory class in order to create
 * different types of messages
 */
class MessageFactory {

    createMessageElement(messageJson) {
        if (messageJson.messageImage !== null) {
            let imageMessage = new ImageMessageView(messageJson);
            return imageMessage.messageView;
        } else {
            let textMessage = new TextMessage(messageJson);
            return textMessage.messageView;
        }
    }
}


class ImageMessageView {
    constructor(messageJson) {
        let messageHtml = '<div><img src="' + messageJson.messageImage + '"class="message-image"></div>';
        let domParser = new DOMParser();
        let messageView = domParser.parseFromString(messageHtml, "text/html").getElementsByTagName('div')[0];
        if (messageJson.senderId === sessionUserId) {
            messageView.style.textAlign = "right";
        }
        this.messageView = messageView;
    }

}

class TextMessage {
    constructor(messageJson) {
        let messageHtml = '<span style="display: block; ">' + messageJson.messageContent + '</span>';
        let domParser = new DOMParser();
        let messageView = domParser.parseFromString(messageHtml, "text/html").getElementsByTagName('span')[0];
        if (messageJson.senderId === sessionUserId) {
            messageView.style.textAlign = "right";
        }
        this.messageView = messageView;
    }

}


function removeChat(chat) {
    chatWindow.stopAsyncFunctions();
    document.body.removeChild(chat);
    chatWindow = undefined;

}

/**
 * Call this method in order to create a new chat window
 * It automatically resets the parameters in the database
 * on close
 * @param currentUserId
 * @param receiverId
 * @param username
 */
function startChat(receiverId, username) {

    function resetDatabase() {
        markCurrentUserAsTyping(false);
    }

    window.onbeforeunload = resetDatabase;

    if (chatWindow === undefined) {
        chatWindow = new ChatWindow(username, receiverId);
        fetchOldMessages();
        chatWindow.requestFocusOnTextArea();
        setTimeout(chatWindow.scrollToLastFetchedMessage(), 1000);
    }
}

function attachIdleCheck() {
    if (chatWindow.idleCheck === undefined) {
        attachMouseListener();
        chatWindow.idleCheck = setInterval(checkMouseLastMovedTime, 1000);

        function attachMouseListener() {
            document.body.onmousemove = () => {
                lastMouseMovedTime = new Date().getTime();
            }
        }

        function checkMouseLastMovedTime() {
            if (chatWindow !== undefined) {
                if (lastMouseMovedTime + timeBeforeIdleMode < new Date().getTime()) {
                    enterIdleMode();
                } else {
                    chatWindow.initializeDatabaseCheck();
                    setIntervalNewMessages();
                }
            }
        }
    }

}


function initializeMessagingSystem() {
    //fetch the chat id
    let url = "ChatController.php?requestName=fetchChatId&" + "apiKey=" + apiKey;
    url += "&user1Id=" + sessionUserId;
    url += "&user2Id=" + chatWindow.receiverID;

    fetch(url).then(function (response) {
        return response.text();
    }).then(data => {
        chatWindow.chatId = data;
        chatWindow.initializeDatabaseCheck();
        attachIdleCheck();
    });

}


function toggleElement(element) {
    if (element.style.display !== "none") {
        element.style.display = "none";

    } else {
        element.style.display = "block";
    }
}


function toggleChatWindow(chatBody) {
    if (chatBody.style.display !== "none") {
        chatBody.style.display = "none";
    } else {
        chatBody.style.display = "block";
        chatWindow.hideNotification();
    }
}


//*********************************************AJAX FUNCTION *************************************************

function enterIdleMode() {
    clearInterval(unreadMessagesInterval);
    newMessagesCheckRunning = false;
    if (chatWindow !== undefined) {
        chatWindow.stopAsyncFunctions()
    }
}

function uploadImage() {
    chatWindow.fetchMessagesRequestSent = true;
    let url = "ChatController.php?requestName=UploadImage&" + "apiKey=" + apiKey;
    let formData = new FormData();
    const file = document.getElementsByName("chatImage")[0].files[0];
    formData.append("receiverId", chatWindow.receiverID);
    formData.append("currentUserId", sessionUserId);
    formData.append("imageName", file.name);
    resizeImage(file, 250, 200).then(imageData => {
        formData.append('imageData', imageData);
        fetch(url, {
            method: 'POST',
            body: formData,
        }).then(function (response) {
            return response.text();
        }).then(function (data) {
            //prepare message
            let responseObject = JSON.parse(data);
            chatWindow.addNewMessage(responseObject);
            chatWindow.fetchMessagesRequestSent = false;
        })

    })
}

/**
 * This function is used to send a message to the user with the specified receiverID
 *
 * @param receiverID
 */
function sendMessage() {
    let messageField = document.getElementById("messageField");
    let message = messageField.value.trim();
    if (message !== "") {
        chatWindow.fetchMessagesRequestSent = true;
        let formData = new FormData();
        let url = "ChatController.php?requestName=sendMessage&" + "apiKey=" + apiKey;
        formData.append("receiverId", chatWindow.receiverID);
        formData.append("currentUserId", sessionUserId);
        formData.append("messageContent", message);
        fetch(url, {
            method: 'POST',
            body: formData,
        }).then(function (response) {
            return response.text();
        }).then(function (data) {
            let responseObject = JSON.parse(data);
            chatWindow.addNewMessage(responseObject);
            chatWindow.fetchMessagesRequestSent = false;
        });

        messageField.value = "";
    }
}


function checkUser2IsTyping() {
    let url = "ChatController.php?requestName=checkUser2IsTyping&" + "apiKey=" + apiKey;
    url += "&userId=" + sessionUserId;
    url += "&chatId=" + chatWindow.chatId;
    fetch(url).then(function (response) {
        return response.text();
    }).then(data => {
        let responseObject = JSON.parse(data);
        if (chatWindow !== undefined) {
            chatWindow.displayUserTypingHint(responseObject.userIsTyping);
        }
    });

}

function fetchNewMessages(receiverId) {
    let url = "ChatController.php?requestName=fetchNewMessages&" + "apiKey=" + apiKey;
    url += "&lastMessageId=" + chatWindow.lastMessageID;
    url += "&currentUserId=" + sessionUserId;
    url += "&receiverId=" + receiverId;

    if (chatWindow.fetchMessagesRequestSent === false) {
        chatWindow.fetchMessagesRequestSent = true;
        fetch(url).then(function (response) {
            return response.text();
        }).then(function (data) {
            if (chatWindow !== undefined) {
                let messageArray = JSON.parse(data);
                chatWindow.addNewMessagesToContainer(messageArray);
                chatWindow.fetchMessagesRequestSent = false;
            }
        });
    }
}


function markCurrentUserAsTyping(isTyping) {
    let url = "ChatController.php?requestName=markTyping&" + "apiKey=" + apiKey;
    let formData = new FormData();
    formData.append("userId", sessionUserId);
    formData.append("chatId", chatWindow.chatId);
    formData.append("isTyping", isTyping);
    fetch(url, {
        method: 'POST',
        body: formData,
    }).then(function (response) {
        return response.text();
    });

}


function checkCurrentUserTyping() {
    let currentTime = new Date().getTime();
    if (chatWindow.lastKeyPressedTime + 3000 < currentTime) {
        markCurrentUserAsTyping(false);
    } else {
        markCurrentUserAsTyping(true);
    }
}


/**
 * This method is used in order to fetch old messages in a chat
 * Depending on the global variable currentlyDisplayedMessages
 * It is efficient to only fetch old messages when the user wants to
 *
 * @param receiverID
 */
function fetchOldMessages() {
    let url = "ChatController.php?requestName=fetchOldMessages&" + "apiKey=" + apiKey;
    url += "&currentUserId=" + sessionUserId;
    url += "&receiverId=" + chatWindow.receiverID;
    url += "&offset=" + chatWindow.currentlyDisplayedMessages;

    chatWindow.fetchMessagesRequestSent = true;
    fetch(url).then(function (response) {
        return response.text();
    }).then(data => {
        let jsonArray = JSON.parse(data);
        chatWindow.addOldMessagesToContainer(jsonArray);
        chatWindow.fetchMessagesRequestSent = false;


        // //the first time the function fetch old messages is called
        if (chatWindow.chatId === undefined) {
            if (jsonArray.length > 0) {
                chatWindow.lastMessageID = jsonArray[0].messageId;
                chatWindow.scrollToLastFetchedMessage();
            }
            initializeMessagingSystem();

        }
    });
}


function markMessagesAsSeen() {
    let url = "ChatController.php?requestName=markMessagesAsSeen&" + "apiKey=" + apiKey;
    let formData = new FormData();
    formData.append("currentUserId", sessionUserId);
    formData.append("receiverId", chatWindow.receiverID);

    fetch(url, {
        method: 'POST',
        body: formData,
    }).then(data => {
        if (chatWindow !== undefined) {
            let friendElement = document.getElementById("friend_container_" + chatWindow.receiverID);
            friendElement.style.background = "";
        }
    });

}


function playNotificationSound() {
    document.getElementById("notificationAudio").play();
}

function setIntervalNewMessages() {
    if (newMessagesCheckRunning === false) {
        newMessagesCheckRunning = true;
        unreadMessagesInterval = setInterval(checkHasUnreadMessages, 5000);
    }
}

function checkHasUnreadMessages() {
    let url = "ChatController.php?requestName=checkHasNewMessages&" + "apiKey=" + apiKey;
    url += "&currentUserId=" + sessionUserId;
    fetch(url).then(response => {
        return response.text();
    }).then(data => {
        let jsonData = JSON.parse(data);
        for (let i = 0; i < jsonData.length; i++) {
            let userID = jsonData[i];
            if (chatWindow === undefined || chatWindow.receiverID !== userID) {
                document.getElementById("friend_container_" + userID).style.background = "#ffb465";
            }
        }
    });
}








