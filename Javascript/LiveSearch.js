
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


function fetchPostSuggestions(query) {
    if (query.length > 1) {
        let url = "LiveSearchController.php?postsSearchQuery=" + query;
        getXmlHttpGetRequest(url).onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                if (this.responseText !== "No results") {
                    let jsonObject = JSON.parse(this.responseText);
                    console.log(jsonObject);
                }
            }
        };
    }
}

//put a get request inside this
function insertFetchedSuggestions(suggestionsJson) {
    let searchField = document.getElementById("search-posts-field");
    let currentFocus;
    /*execute a function when someone writes in the text field:*/
    let container, suggestedItem, i, val = searchField.value;
    /*close any already open lists of autocompleted values*/
    closeAllLists();
    if (!val) {
        return false;
    }
    container = document.createElement("div");
    container.setAttribute("id", this.id + "insertFetchedSuggestions-list");
    container.setAttribute("class", "insertFetchedSuggestions-items");
    //insert the suggestions container as a child in the search field
    searchField.parentNode.appendChild(container);
    suggestedItem = document.createElement("DIV");
    //make the matching letters bold
//    suggestedItem.innerHTML = "<strong>" + suggestions[i].substr(0, val.length) + "</strong>";
    suggestedItem.innerHTML += "cactus";
    /*insert a input field that will hold the current array item's value:*/
    suggestedItem.innerHTML += "<input type='hidden' value='" + "cactus" + "'>";
    /*execute a function when someone clicks on the item value (DIV element):*/
    suggestedItem.addEventListener("click", function (e) {
        //this is when the user pressed a suggestion item
        closeAllLists();
    });
    container.appendChild(suggestedItem);
}


//close the list if the user presses somewhere else on the screen
document.addEventListener("click", function (e) {
    closeAllLists(e.target);
});


//call this method in order to hide all auto - suggestions
function closeAllLists(elmnt) {
    let x = document.getElementsByClassName("autocomplete-items");
    for (let i = 0; i < x.length; i++) {
        if (elmnt !== x[i] && elmnt !== searchField) {
            x[i].parentNode.removeChild(x[i]);
        }
    }
}