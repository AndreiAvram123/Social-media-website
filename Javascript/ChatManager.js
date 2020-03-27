//check the database every third of  a second
let timeIntervalCheck = 1500;
let timeIntervalCheckTyping = 1500;
let intervalCheck;
let userIsTypingCheck;
let idleCheck;
let currentUserIsTypingCheck;
let sessionUserId;
let lastKeyPressedTime;
let shouldFetchNewMessages = true;
let chatWindow;
let lastMouseMovedTime;
//in milliseconds
let timeBeforeIdleMode = 2000;

class ChatWindow {
    constructor(username, receiverId) {
        //use a dom parser to convert html into a document element
        this.scrollTopFetchOffset = 200;
        let domParser = new DOMParser();
        //the html element for a chat window
        let chatString = '<div class="message-window" >\n' +
            '<i class="fas fa-times float-right"></i>' +
            '    <div class="message-header" onclick="toggleElement(document.getElementById(' + '\'' + 'message-window-body' + '\')' + ')">' +
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
            ' <input type="file"  name="files[]"  style="display: none"' +
            '    </div>\n' +
            '</div>';
        let domElement = domParser.parseFromString(chatString, "text/html");
        this.messageFactory = new MessageFactory();
        this.initializeDefaultParameters(receiverId);
        this.initializeViews(domElement);
        this.attachListeners(domElement, receiverId);
        this.receiverID = receiverId;
        this.asyncFunctionsRunning = false;
        this.lastMessageID = -1;
        document.body.append(this.chatWindow);
    }

    /**
     * This method is used to initialize specific default
     * parameters that are necessary for a chat window
     * to function
     * @param receiverId
     */
    initializeDefaultParameters(receiverId) {
        this.receiverID = receiverId;
        this.currentlyDisplayedMessages = 0;
        this.fetchMessagesRequestSent = false;
        this.noMoreOldMessagesToFetch = false;
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
        this.lastMessageID = lastMessageID;
        chatWindow.scrollToLastFetchedMessage();
    }


    /**
     * This method is used to attach listeners
     * to the chat window
     * @param domElement
     * @param receiverId
     */

    attachListeners(domElement, receiverId) {
        domElement.getElementsByTagName("i")[0].addEventListener('click', event => removeChat(this.chatWindow));
        let imageSelector = domElement.getElementsByName("files[]")[0];
        let imageSelectIcon = domElement.getElementsByClassName("select-image-icon")[0];
        imageSelectIcon.addEventListener('click', event => imageSelector.click());
        imageSelector.addEventListener('change', event => {
            uploadImage(receiverId);
        });
        this.attachScrollListener();
    }

    initializeViews(domElement) {
        this.chatWindow = domElement.documentElement;
        this.userIsTypingHint = domElement.getElementById("user-is-typing-hint");
        this.messageContainer = domElement.getElementsByClassName("message-container")[0];
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
        clearInterval(intervalCheck);
        clearInterval(userIsTypingCheck);
        clearInterval(currentUserIsTypingCheck);
    }


    getMessageContainerTopOffset() {
        return this.messageContainer.scrollTop;
    }


    attachScrollListener() {
        this.messageContainer.addEventListener('scroll', () => {
            if (this.getMessageContainerTopOffset() <= this.scrollTopFetchOffset) {
                if (this.fetchMessagesRequestSent !== true && this.noMoreOldMessagesToFetch === false) {
                    fetchOldMessages(this.receiverID);

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

            this.lastMessageID = messagesJson[messagesJson.length - 1].messageID;
            chatWindow.scrollToLastFetchedMessage();
        }
    }

    scrollToLastFetchedMessage() {
        this.messageContainer.scrollTop = this.messageContainer.scrollHeight;
    }

    initializeMessagesCheckInterval() {
        if (this.asyncFunctionsRunning === false) {
            this.asyncFunctionsRunning = true;
            intervalCheck = setInterval(fetchNewMessages, timeIntervalCheck, this.receiverID, this.messageContainer);
            userIsTypingCheck = setInterval(checkUser2IsTyping, timeIntervalCheckTyping);
            currentUserIsTypingCheck = setInterval(checkCurrentUserTyping, timeIntervalCheckTyping);
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
            let imageMessage = new ImageMessage(messageJson);
            return imageMessage.messageView;
        } else {
            let textMessage = new TextMessage(messageJson);
            return textMessage.messageView;
        }
    }
}


class ImageMessage {
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
function startChat(currentUserId, receiverId, username) {

    function resetDatabase() {
        markCurrentUserAsTyping(false);
    }

//todo
    //change this
    window.onbeforeunload = resetDatabase;
    if (chatWindow === undefined) {
        sessionUserId = currentUserId;
        chatWindow = new ChatWindow(username, receiverId);
        lastKeyPressedTime = new Date().getTime() - 3000;
        (document.getElementById("messageField")).addEventListener('keyup', (event) => {
            if (event.key === "Enter") {
                sendMessage(receiverId);
            } else {
                if (event.key !== "Backspace") {
                    lastKeyPressedTime = new Date().getTime();
                }
            }


        });
        fetchOldMessages(receiverId);
    }
}

function attachIdleCheck() {
    if (idleCheck === undefined) {
        attachMouseListener();
        idleCheck = setInterval(checkMouseLastMovedTime, 1000);

        function attachMouseListener() {
            document.body.onmousemove = (e) => {
                lastMouseMovedTime = new Date().getTime();
            }
        }

        function checkMouseLastMovedTime() {
            if (lastMouseMovedTime + timeBeforeIdleMode < new Date().getTime()) {
                if (chatWindow !== undefined) {
                    chatWindow.stopAsyncFunctions();
                }
            } else {
                chatWindow.initializeMessagesCheckInterval();
            }

        }
    }

}


function getChatId(user2Id) {
    let url = "ChatController.php?requestName=fetchChatId&" + "apiKey=" + apiKey;
    url += "&user1Id=" + sessionUserId;
    url += "&user2Id=" + user2Id;

    fetch(url).then(function (response) {
        return response.text();
    }).then(data => {
        chatWindow.chatId = data;

        chatWindow.initializeMessagesCheckInterval();
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


//*********************************************AJAX FUNCTION *************************************************
function uploadImage(receiverId) {
    chatWindow.fetchMessagesRequestSent = true;
    let url = "ChatController.php?requestName=UploadImage&" + "apiKey=" + apiKey;
    let formData = new FormData();
    const file = document.querySelector('[type=file]').files[0];
    formData.append("receiverId", receiverId);
    formData.append("currentUserId", sessionUserId);
    formData.append("imageName", file.name);
    resizeImage(file).then(imageData => {
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
        })

    })
}

/**
 * This function is used to send a message to the user with the specified receiverID
 *
 * @param receiverID
 */
function sendMessage(receiverID) {
    let messageField = document.getElementById("messageField");
    let message = messageField.value.trim();
    if (message !== "") {
        shouldFetchNewMessages = false;
        let formData = new FormData();
        let url = "ChatController.php?requestName=sendMessage&" + "apiKey=" + apiKey;
        formData.append("receiverId", receiverID);
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
            shouldFetchNewMessages = true;
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
        chatWindow.displayUserTypingHint(responseObject.userIsTyping);
    });

}

function fetchNewMessages(receiverId) {
    let url = "ChatController.php?requestName=fetchNewMessages&" + "apiKey=" + apiKey;
    let formData = new FormData();

    formData.append("lastMessageId", chatWindow.lastMessageID);
    formData.append("currentUserId", sessionUserId);
    formData.append("receiverId", receiverId);


    if (shouldFetchNewMessages === true) {
        shouldFetchNewMessages = false;
        fetch(url, {
            method: 'POST',
            body: formData,
        }).then(function (response) {
            return response.text();
        }).then(function (data) {
            let messageArray = JSON.parse(data);
            chatWindow.addNewMessagesToContainer(messageArray);
            shouldFetchNewMessages = true;
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
    if (lastKeyPressedTime + 2000 < currentTime) {
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
function fetchOldMessages(receiverID) {
    let url = "ChatController.php?requestName=fetchOldMessages&" + "apiKey=" + apiKey;
    url += "&currentUserId=" + sessionUserId;
    url += "&receiverId=" + receiverID;
    url += "&offset=" + chatWindow.currentlyDisplayedMessages;

    chatWindow.fetchMessagesRequestSent = true;
    fetch(url).then(function (response) {
        return response.text();
    }).then(data => {
        let jsonArray = JSON.parse(data);
        chatWindow.addOldMessagesToContainer(jsonArray);
        chatWindow.fetchMessagesRequestSent = false;


        //the first time the function fetch old messages is called
        if (chatWindow.chatId === undefined) {
            if(jsonArray.length>0){
                chatWindow.lastMessageID = jsonArray[0].messageId;
                chatWindow.scrollToLastFetchedMessage();
            }
            getChatId(receiverID);

        }
    });
}
















