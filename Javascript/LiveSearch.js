

function fetchFriendsSuggestions(query) {
    let friendsList = document.getElementById("friend-container");
    let friendsSuggestionsContainer = document.getElementById("friends-suggestions-container");

    function addSuggestionToView(element) {
        friendsSuggestionsContainer.innerHTML +=
            '<div class="suggestion-friend-item clearfix">\n' +
            '                        <img class="float-left" src="' + element.profilePicture + '"/>\n' +
            '  <form method="get" action="ProfilePage.php">\n' +
            '\n' +
            '            <button type="submit" class="link-button" name="profileButton">\n' +
            '               ' + element.username + '</button>\n' +
            '            <input type="hidden" name="authorIDValue" value="' + element.userId + '">\n' +
            '\n' +
            '        </form>\n' +
            '                    </div>'
    }

    function processResponse(jsonResponse) {
        if (jsonResponse != null) {
            let jsonArray = JSON.parse(jsonResponse);
            jsonArray.forEach(element => {
                addSuggestionToView(element)
            })
        }
    }

    friendsSuggestionsContainer.innerHTML = "";
    if (query.length > 1) {
        friendsList.style.display = "none";
        let url = "LiveSearchController.php?query=" + query;
        getXmlHttpGetRequest(url).onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                if (this.responseText !== "No results") {
                    processResponse(this.responseText);
                } else {
                    processResponse(null);
                }
            }
        };
    } else {
        friendsList.style.display = "block";
    }
}
function fetchPostSuggestions($query){

}