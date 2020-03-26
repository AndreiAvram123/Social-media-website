let postsSuggestionContainer;
initializePostSuggestionsContainer();

class SuggestionFactory {
    createSuggestion(type, jsonData) {
        switch (type) {
            case "post_suggestion":
                return new PostSuggestionItem(jsonData).suggestionView;
            case "friend_suggestion":
                return new FriendSuggestionItem(jsonData).suggestionView;
        }
    }
}

const suggestionFactory = new SuggestionFactory();


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
        this.suggestionView = domParser.parseFromString(htmlString, "text/html").getElementsByClassName("suggestion-friend-item clearfix")[0];

    }

}

class PostSuggestionItem {
    constructor(suggestionJson) {
        let postTitle = suggestionJson.postTitle;
        let postID = suggestionJson.postID;
        /*check if the item starts with the same letters as the text field value:*/
        /*create a DIV element for each matching element:*/
        this.suggestionView = document.createElement("DIV");
        //make the matching letters bold
        this.suggestionView.innerHTML += postTitle;
        /*execute a function when someone clicks on the item value (DIV element):*/
        this.suggestionView.addEventListener("click", function (e) {
            performSearchByPostID(postID);
        });
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
            friendsSuggestionsContainer.appendChild(suggestionFactory.createSuggestion("friend_suggestion", element));
        })
    }

    if ((event.keyCode >= '65' && event.keyCode <= '90') || event.keyCode === 8) {
        if (query.length > 1) {
            let url = "LiveSearchController.php?query=" + query + "&apiKey=" + apiKey;
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
        let url = "LiveSearchController.php?postsSearchQuery=" + query + "&encrypted=true" + "&apiKey=" + apiKey;
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
    let searchField = document.getElementById("search-posts-field");
    clearSuggestionsList();

    //insert the suggestions postsSuggestionContainer as a child in the search field
    searchField.parentNode.appendChild(postsSuggestionContainer);
    //insert all available suggestions
    suggestionsJSONArray.forEach(suggestion => {
        postsSuggestionContainer.appendChild(suggestionFactory.createSuggestion("post_suggestion", suggestion));

    });


//call this method in order to hide all auto - suggestions
    function clearSuggestionsList() {
        postsSuggestionContainer.innerHTML = "";
    }
}
