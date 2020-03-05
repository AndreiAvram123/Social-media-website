let postsSuggestionContainer;

initializePostSuggestionsContainer();


function initializePostSuggestionsContainer() {
    postsSuggestionContainer = document.createElement("div");
    postsSuggestionContainer.setAttribute("id", this.id + "autocomplete-list");
    postsSuggestionContainer.setAttribute("class", "autocomplete-items");
}

class FriendSuggestionItem {
    constructor(elementData) {
        let domParser = new DOMParser();
        let htmlString = '<div class="suggestion-friend-item clearfix">\n' +
            '                        <img class="float-left" src="' + elementData.profilePicture + '"/>\n' +
            '  <form method="get" action="ProfilePage.php">\n' +
            '\n' +
            '            <button type="submit" class="link-button" name="profileButton">\n' +
            '               ' + elementData.username + '</button>\n' +
            '            <input type="hidden" name="authorIDValue" value="' + elementData.userId + '">\n' +
            '\n' +
            '        </form>\n' +
            '                    </div>';
        this.elementBody = domParser.parseFromString(htmlString, "text/html");

    }

    /**
     * Return the view associated with this object
     * @returns {Element}
     */
    getView() {
        return this.elementBody.getElementsByClassName("suggestion-friend-item clearfix")[0];
    }

}

function fetchFriendsSuggestions(event, query) {
    let friendsSuggestionsContainer = document.getElementById("friends-suggestions-container");
    let friendsList = document.getElementById("friend-container");

    function closeFriendsSuggestionsContainer() {
        friendsList.style.display = "block";
        friendsSuggestionsContainer.style.display = "none";
    }

    function openFriendsSuggestionsContainer() {
        friendsList.style.display = "none";
        friendsSuggestionsContainer.style.display = "block";
        friendsSuggestionsContainer.innerHTML = "";
    }

    function processResponse(jsonArray) {
        openFriendsSuggestionsContainer();
        jsonArray.forEach(element => {
            let suggestionItem = new FriendSuggestionItem(element);
            friendsSuggestionsContainer.appendChild(suggestionItem.getView());
        })
    }

    if ((event.keyCode >= '65' && event.keyCode <= '90') || event.keyCode == 8) {
        if (query.length > 1) {
            let url = "LiveSearchController.php?query=" + query;
            fetch(url).then(function (response) {
                return response.text();
            }).then(data => {
                processResponse(JSON.parse(data));
            });

        } else {
            closeFriendsSuggestionsContainer();
        }
    }
}

function fetchPostSuggestions(query) {
    function getPostSuggestionsUrl() {
        //get the filters from the filter modal and display suggestions accordingly
        let sortDate = document.getElementById("postOrder").value;
        let category = document.getElementById("postCategorySelector").value;
        let url = "LiveSearchController.php?postsSearchQuery=" + query;
        if (sortDate !== "None") {
            url += "&sortDate=" + sortDate;
        }
        if (category !== "All") {
            url += "&category=" + category;
        }
        return url;
    }

    if (query.length > 1) {
        let url = getPostSuggestionsUrl(query);
        fetch(url).then(function (response) {
            return response.text();
        }).then(data => {
            let jsonObject = JSON.parse(data);
            insertFetchedSuggestions(jsonObject);
        });
    } else {
        postsSuggestionContainer.innerHTML = "";
    }
}

function performSearchByPostID(id) {
    window.location.href = "CurrentPost.php?valuePostID=" + id;
}

function insertFetchedSuggestions(suggestionsJSONArray) {
    let currentFocus;
    /*execute a function when someone writes in the text field:*/
    let searchField = document.getElementById("search-posts-field");
    let suggestedItem, i, val = searchField.value;
    /*close any already open lists of autocompleted values*/
    clearSuggestionsList();
    if (!val) {
        return false;
    }
    currentFocus = -1;

    //insert the suggestions postsSuggestionContainer as a child in the search field
    searchField.parentNode.appendChild(postsSuggestionContainer);
    //insert all available suggestions
    suggestionsJSONArray.forEach(suggestion => {
        let postTitle = suggestion.postTitle;
        let postID = suggestion.postID;
        /*check if the item starts with the same letters as the text field value:*/
        /*create a DIV element for each matching element:*/
        suggestedItem = document.createElement("DIV");
        //make the matching letters bold
        suggestedItem.innerHTML += postTitle;
        /*execute a function when someone clicks on the item value (DIV element):*/
        suggestedItem.addEventListener("click", function (e) {
            performSearchByPostID(postID);
        });
        postsSuggestionContainer.appendChild(suggestedItem);

    });


//call this method in order to hide all auto - suggestions
    function clearSuggestionsList() {
        postsSuggestionContainer.innerHTML = "";
    }
}

function getXmlHttpGetRequest(url) {
    let xhttp = new XMLHttpRequest();
    xhttp.open("GET", url, true);
    xhttp.send();
    return xhttp;
}



