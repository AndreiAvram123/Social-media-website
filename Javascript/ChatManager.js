//check the database every third of  a second
let timeIntervalCheck = 300;
let timeIntervalCheckTyping = 700;
let intervalCheck;
let userIsTypingCheck;
let currentUserIsTypingCheck;
let sessionUserId;
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

        this.initializeDefaultParameters(receiverId);
        this.initializeViews(domElement);
        this.attachListeners(domElement, receiverId);
        document.body.append(this.chatWindow);
    }

    initializeDefaultParameters(receiverId) {
        this.receiverID = receiverId;
        this.currentlyDisplayedMessages = 0;
        this.fetchMessagesRequestSent = false;
        this.noMoreOldMessagesToFetch = false;
        this.chatWindowInitialized = false;
    }

    addOldMessagesToContainer(messages) {
        let messageContainer = this.messageContainer;
        messages.forEach((messageView) => {
            if (messageContainer.childNodes.length > 0) {
                messageContainer.insertBefore(messageView, this.messageContainer.childNodes[0]);
            } else {
                messageContainer.appendChild(messageView);
            }
            this.currentlyDisplayedMessages++;
        });

    }

    attachListeners(domElement, receiverId) {
        domElement.getElementsByTagName("i")[0].addEventListener('click', event => removeChat(this.chatWindow));
        let imageSelector = domElement.getElementsByName("files[]")[0];
        let imageSelectIcon = domElement.getElementsByClassName("select-image-icon")[0];
        imageSelectIcon.addEventListener('click', event => imageSelector.click());
        imageSelector.addEventListener('change', event => {
            uploadImage(receiverId);
        });
    }

    initializeViews(domElement) {
        this.chatWindow = domElement.documentElement;
        this.userIsTypingHint = domElement.getElementById("user-is-typing-hint");
        this.messageContainer = domElement.getElementsByClassName("message-container")[0];
    }

    getViewsForMessages(messagesJson) {
        let playNotification = false;
        let messagesViews = [];
        messagesJson.forEach(messageJson => {
            let messageView;
            if (messageJson.messageImage == null) {
                messageView = this.getMessageTextView(messageJson);
            } else {
                messageView = this.getMessageImageView(messageJson);
            }
            if (messageJson.senderId === sessionUserId) {
                messageView.style.textAlign = "right";
            } else {
                playNotification = true;
            }
            messagesViews.push(messageView);

        });
        return messagesViews;
    }

    getMessageImageView(messageJson) {
        let messageView = document.createElement("div");
        messageView.className = "float-right";
        let messageImage = document.createElement("img");
        messageImage.src = messageJson.messageImage;
        messageImage.className = "message-image";
        messageView.appendChild(messageImage);
        return messageView;
    }

    getMessageTextView(messageJson) {
        let messageView = document.createElement("span");
        messageView.style.display = "block";
        messageView.innerText = messageJson.messageContent;
        return messageView;
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

    getMessageContainerTopOffset() {
        return this.messageContainer.scrollTop;
    }

    attachScrollListener() {
        this.messageContainer.addEventListener('scroll', event => {
            if (this.fetchMessagesRequestSent !== true && this.getMessageContainerTopOffset() <= 100) {
                if (this.noMoreOldMessagesToFetch === false) {
                    this.fetchMessagesRequestSent = true;
                    fetchOldMessages(this.receiverID);
                }
            }
        })
    }


    addNewMessagesToContainer(messages) {
        messages.forEach((messageView) => {
            this.messageContainer.appendChild(messageView);
            this.currentlyDisplayedMessages++;
        });
        chatWindow.scrollToLastFetchedMessage();
    }

    scrollToLastFetchedMessage() {
        this.messageContainer.scrollTop = this.messageContainer.scrollHeight;
    }
}

function fetchOldMessages(receiverID) {
    let url = "ChatController.php?requestName=fetchOldMessages";
    url += "&currentUserId=" + sessionUserId;
    url += "&receiverId=" + receiverID;
    url += "&offset=" + chatWindow.currentlyDisplayedMessages;
    getXmlHttpGetRequest(url).onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            if (this.responseText !== "No results") {
                chatWindow.fetchMessagesRequestSent = false;
                let jsonArray = JSON.parse(this.responseText);
                chatWindow.addOldMessagesToContainer(chatWindow.getViewsForMessages(jsonArray));
                //if the fetchOldMessages function has been called
                //the first time then chatWindowInitialized is false
                if (chatWindow.chatWindowInitialized === false) {
                    chatWindow.chatWindowInitialized = true;
                    chatWindow.lastMessageID = jsonArray[0].messageId;
                    chatWindow.scrollToLastFetchedMessage();
                    initializeOtherAsyncFunctions(receiverID);
                    chatWindow.attachScrollListener();
                }
            }
        } else {
            this.noMoreOldMessagesToFetch = true;
        }
    }
}


function removeChat(chat) {
    chatWindow.stopAsyncFunctions();
    document.body.removeChild(chat);
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

    window.onbeforeunload = resetDatabase;
    if (chatWindow === undefined ) {
        sessionUserId = currentUserId;
        chatWindow = new ChatWindow(username, receiverId);
        (document.getElementById("messageField")).addEventListener('keyup', (event) => {
            if (event.key === "Enter") {
                sendMessage(receiverId);
            } else {
                if (event.key !== "Backspace") {
                    markCurrentUserAsTyping(true);
                }
            }


        });
        fetchOldMessages(receiverId);
    }
}

function initializeOtherAsyncFunctions(user2Id) {

    function getChatId() {
        let url = "ChatController.php?requestName=fetchChatId";
        url += "&user1Id=" + sessionUserId;
        url += "&user2Id=" + user2Id;
        getXmlHttpGetRequest(url).onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                chatWindow.chatId = this.responseText;

            }
        }
    }

    getChatId();

    function initializeAsyncFunctions() {
        intervalCheck = setInterval(fetchNewMessages, timeIntervalCheck, user2Id, this.messageContainer);
        userIsTypingCheck = setInterval(checkUserIsTyping, timeIntervalCheckTyping);
        currentUserIsTypingCheck = setInterval(checkCurrentUserTyping, timeIntervalCheckTyping);
    }

    initializeAsyncFunctions();
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
    url += "&chatId=" + chatWindow.chatId;
    getXmlHttpGetRequest(url).onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            if (this.responseText === 1) {
                chatWindow.displayUserTypingHint(true);
            } else {
                chatWindow.displayUserTypingHint(false)
            }
        }

    };
}

function fetchNewMessages(receiverId) {
    let url = "ChatController.php?requestName=fetchNewMessages";
    url += "&lastMessageId=" + chatWindow.lastMessageID;
    url += "&currentUserId=" + sessionUserId;
    url += "&receiverId=" + receiverId;
    if (shouldFetchNewMessages === true && chatWindow.lastMessageID !== undefined) {
        shouldFetchNewMessages = false;
        getXmlHttpGetRequest(url).onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                if (this.responseText !== defaultResponseNoData) {
                    let jsonArray = JSON.parse(this.responseText);
                    chatWindow.lastMessageID = jsonArray[jsonArray.length - 1].messageId;
                    let messagesViews = chatWindow.getViewsForMessages(jsonArray);
                    chatWindow.addNewMessagesToContainer(messagesViews);
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


function markCurrentUserAsTyping(isTyping) {
    let dataToSend = "ChatController.php?requestName=markTyping";
    dataToSend += "&userId=" + sessionUserId;
    dataToSend += "&chatId=" + chatWindow.chatId;
    dataToSend += "&isTyping=" + isTyping;
    getXmlHttpGetRequest(dataToSend);
}


function checkCurrentUserTyping() {
    let currentTime = new Date().getTime();
    if (lastKeyPressedTime + 20 < currentTime) {
        markCurrentUserAsTyping(false);
    }
}


















