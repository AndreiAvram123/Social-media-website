let messageField = document.getElementById("messageField");
function sendMessage() {
    let message = messageField.value;
    let dataToSend = "messageContent=" + message;
    let xhttp = new XMLHttpRequest();
    xhttp.open("POST", "Data/MessageAsync.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(dataToSend);
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
           console.log(this.responseText);
        }
    };
}
