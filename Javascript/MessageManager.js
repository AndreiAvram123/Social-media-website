//check the database every second
let timeIntervalCheck = 1000;
let intervalCheck;
let lastMessageDate;
let sessionUserId;
let windowInitialized = false;

function fetchNewMessages(receiverId, container) {
    let url = "asyncControllers/ChatController.php?requestName=fetchNewMessages";
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
    let message = messageField.value;
    //  let messageContainer = document.getElementById("messageContainer");
    if (message.trim() !== "") {
        let dataToSend = "messageContent=" + message;
        dataToSend += "&receiverId=" + receiverId;
        dataToSend += "&currentUserId=" + sessionUserId;
        let xhttp = new XMLHttpRequest();
        xhttp.open("POST", "asyncControllers/ChatController.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send(dataToSend);
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
    if (windowInitialized === false) {
        sessionUserId = currentUserId;
        document.body.innerHTML += '<div class="message-window" >\n' +
            '    <div class="message-header" onclick="updateMessageWindowVisibility()">' +
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

function updateMessageWindowVisibility() {

    let messageContainer = document.getElementById("message-window-body");
    if (messageContainer.style.display === "block") {
        messageContainer.style.display = "none";
    } else {
        messageContainer.style.display = "block";
    }
}

function processMessages(data, container) {
    let jsonDataArray = JSON.parse(data);
    jsonDataArray.forEach(elementData => {
        let messageView = getMessageView(elementData);
        container.appendChild(messageView);
    });
    lastMessageDate = jsonDataArray[jsonDataArray.length - 1].messageDate;


    function getMessageView(messageData) {
        let messageView = document.createElement("p");
        messageView.innerText = messageData.messageContent;
        if (messageData.sender_id === sessionUserId) {
            messageView.style.textAlign = "right";
        }
        return messageView;
    }

}


function fetchChatMessages(user2Id, container) {
    let url = "asyncControllers/ChatController.php?requestName=fetchMessages";
    url += "&user1Id=" + sessionUserId;
    url += "&user2Id=" + user2Id;
    getXmlHttpGetRequest(url).onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            console.log(this.responseText);
            if (this.responseText.trim() !== "No results") {
                processMessages(this.responseText, container, sessionUserId);
                intervalCheck = setInterval(fetchNewMessages, timeIntervalCheck, user2Id, container);
            }
        }
    };

}
