let postsSuggestionContainer;
initializePostSuggestionsContainer();
let abortController = new AbortController();
let signal = abortController.signal;

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
            '                        <img class="float-left" src="' + elementData.profilePicture + '&size=100x100' + '"/>\n' +
            '  <form method="get" action="ProfilePage.php">\n' +
            '\n' +
            '            <button type="submit" class="link-button" name="profileButton">\n' +
            '               ' + elementData.username + '</button>\n' +
            '            <input type="hidden" name="authorIDValue" value="' + elementData.userID + '">\n' +
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
        let showImages = document.getElementById("showImagesCheckbox").checked;


        this.suggestionView = document.createElement("div");
        if (showImages) {
            let image = document.createElement("img");
            image.className = "postSuggestionImage";
            image.src = "ImageController.php?imageName=" +  suggestionJson.postImage + "&width=70";
            this.suggestionView.append(image);
        }

        this.suggestionView.innerHTML += postTitle;

        /*execute a function when someone clicks on the item value (DIV element):*/
        this.suggestionView.addEventListener("click", function (e) {
            performSearchByPostID(postID);
        });
    }
}


function abortCurrentRequest() {
    abortController.abort();
    abortController = new AbortController();
    signal = abortController.signal;
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
        //cancel the last call
        abortCurrentRequest();
        let url = getPostSuggestionsUrl(query);
        fetch(url, {
            method: 'get',
            signal: signal,
        }).then(function (response) {
            return response.text();
        }).then(data => {
            let jsonArray = JSON.parse(data);
            insertFetchedSuggestions(jsonArray);
        }).catch(err => {
            if (err.name === "AbortError") {
                console.log("new search performed..");
            }
        });
    } else {
        postsSuggestionContainer.innerHTML = "";
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
            abortCurrentRequest();
            let url = "LiveSearchController.php?query=" + query + "&apiKey=" + apiKey;
            fetch(url, {method: 'get', signal: signal}).then(function (response) {
                return response.text();
            }).then(data => {
                processResponse(JSON.parse(data));
            }).catch(err => {
                if (err.name === "AbortError") {
                    console.log("new search performed..");
                }
            });

        } else {
            closeFriendsSuggestionsContainer();
        }
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

