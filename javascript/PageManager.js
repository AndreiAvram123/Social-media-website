function startChat(){
  document.body.innerHTML +='<div id="message-window" style="position:fixed;bottom: 10px;width: 20%;right:18%;background: #EBE2E2;">-->\n' +
      '            <div style=" background: #007BFF" onclick="\n' +
      '              let messageBody = document.getElementById(\'message-window-body\');\n' +
      '\n' +
      '              if(messageBody.style.display === \'block\'){\n' +
      '                 messageBody.style.display = \'none\';\n' +
      '              }else{\n' +
      '                  messageBody.style.display = \'block\';\n' +
      '              }\n' +
      '            ">\n' +
      '                <p class="text-center" style="color: white">Nela Ion</p>\n' +
      '            </div>\n' +
      '            <div id="message-window-body">\n' +
      '                <div style="overflow:auto;height: 40vh">\n' +
      '\n' +
      '                </div>\n' +
      '                <div style="display: flex;">\n' +
      '                    <textarea style=" width: 100%;" type="text" id="messageField"></textarea>\n' +
      '                    <button style="background:#007BFF;color: white" onclick="sendMessage()"><i\n' +
      '                                class="far fa-paper-plane"></i></button>\n' +
      '                </div>\n' +
      '            </div>\n' +
      '        </div>\n' +
      '        <script src="Data/MessageManager.js"></script>\n'
}