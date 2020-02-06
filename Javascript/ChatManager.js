//check the database every second
let timeIntervalCheck = 1000;
let intervalCheck;
let lastMessageDate;
let sessionUserId;
let chatWindowInitialized = false;

function fetchNewMessages(receiverId, container) {
    let url = "ChatController.php?requestName=fetchNewMessages";
    url += "&lastMessageDate=" + lastMessageDate;
    url += "&currentUserId=" + sessionUserId;
    url += "&receiverId=" + receiverId;

    if (lastMessageDate != null) {
        getXmlHttpGetRequest(url).onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                if (this.responseText !== "No results") {
                    processMessages(this.responseText, container);
                }
            }
        };
    }
}

function sendMessage(receiverId) {
    let messageField = document.getElementById("messageField");
    let message = messageField.value.trim();
    let messageContainer = document.getElementsByClassName("message-container")[0];
    if (message !== "") {

        let dataToSend = "messageContent=" + message;
        dataToSend += "&receiverId=" + receiverId;
        dataToSend += "&currentUserId=" + sessionUserId;
        let xhttp = new XMLHttpRequest();

        xhttp.open("POST", "ChatController.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send(dataToSend);
        xhttp.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                console.log(this.responseText);
                let messageJson = JSON.parse(this.responseText);
                addMessageToChat(messageJson, messageContainer);
            }

        }
        messageField.value = "";
    }
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
            '        <div class="message-footer">\n' +
            '            <textarea type="text" id="messageField" ></textarea>\n' +
            '            <button onclick="sendMessage(' + '\'' + receiverId + '\'' + ')"><i\n' +
            '                    class="far fa-paper-plane"></i></button>\n' +
            '        </div>\n' +
            '    </div>\n'
        '</div>';

        (document.getElementById("messageField")).addEventListener('keypress', (event) => {
            if (event.keyCode === 13) {
                sendMessage(receiverId);
            }
        });
        fetchChatMessages(receiverId, document.getElementsByClassName("message-container")[0]);
    }
}

function removeChat(chat) {
    document.body.removeChild(chat);
    chatWindowInitialized = false;
}

function processMessages(data, container) {
    let jsonDataArray = JSON.parse(data);
    jsonDataArray.forEach(elementData => {
        addMessageToChat(elementData, container);
    });
}

function addMessageToChat(messageJson, container) {
    let messageView = document.createElement("p");
    messageView.innerText = messageJson.messageContent;
    if (messageJson.senderId === sessionUserId) {
        messageView.style.textAlign = "right";
    }
    lastMessageDate = messageJson.messageDate;
    console.log(lastMessageDate);
    container.appendChild(messageView);
    scrollToLastMessage(container);

}

function scrollToLastMessage(container) {
    container.scrollTop = container.lastChild.offsetTop;
}


function fetchChatMessages(user2Id, container) {
    let url = "ChatController.php?requestName=fetchMessages";
    url += "&user1Id=" + sessionUserId;
    url += "&user2Id=" + user2Id;
    getXmlHttpGetRequest(url).onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            if (this.responseText.trim() !== "No results") {
                console.log(this.responseText);
                processMessages(this.responseText, container, sessionUserId);
                intervalCheck = setInterval(fetchNewMessages, timeIntervalCheck, user2Id, container);
            }
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


















