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

    getView() {
        return this.elementBody.getElementsByClassName("suggestion-friend-item clearfix")[0];
    }

}

function fetchFriendsSuggestions(event, query) {
    let friendsList = document.getElementById("friends-suggestions-container");
    let friendsSuggestionsContainer = document.getElementById("friend-container");

    function processResponse(jsonResponse) {
        if (jsonResponse !== undefined) {
            let jsonArray = JSON.parse(jsonResponse);
            jsonArray.forEach(element => {
                let suggestionItem = new FriendSuggestionItem(element);
                console.log(suggestionItem.getView());
                friendsSuggestionsContainer.appendChild(suggestionItem.getView());
            })
        }
    }

    if (event.keyCode >= '65' && event.keyCode <= '90') {
        if (query.length > 1) {
            friendsSuggestionsContainer.innerHTML = "";
            friendsList.style.display = "none";
            let url = "LiveSearchController.php?query=" + query;

            getXmlHttpGetRequest(url).onreadystatechange = function () {
                if (this.readyState === 4 && this.status === 200) {
                    if (this.responseText !== "No results") {
                        processResponse(this.responseText);
                    } else {
                        processResponse(undefined);
                    }
                }
            };
        } else {
            friendsList.style.display = "block";
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
        getXmlHttpGetRequest(url).onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                if (this.responseText !== "No results") {
                    let jsonObject = JSON.parse(this.responseText);
                    insertFetchedSuggestions(jsonObject);
                }
            }
        };
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



