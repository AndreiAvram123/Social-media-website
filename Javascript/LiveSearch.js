let container;
initializeSuggestionsContainer();

function initializeSuggestionsContainer(){
    container = document.createElement("div");
    container.setAttribute("id", this.id + "autocomplete-list");
    container.setAttribute("class", "autocomplete-items");
}

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

    function getPostSuggestionsUrl() {
        //get the filters from the filter modal and display suggestions accordingly
        let sortDate = document.getElementById("postOrder").value;
        let category = document.getElementById("postCategorySelector").value;
        let url = "LiveSearchController.php?postsSearchQuery=" + query;
        url += "&sortDate=" + sortDate;
        url += "&category=" + category;
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
    }else{
      container.innerHTML ="";
    }
}

function insertFetchedSuggestions(suggestionsJSONArray) {
    let currentFocus;
    /*execute a function when someone writes in the text field:*/
    let searchField = document.getElementById("search-posts-field");
    let  suggestedItem, i, val = searchField.value;
    /*close any already open lists of autocompleted values*/
    closeAllLists();
    if (!val) {
        return false;
    }
    currentFocus = -1;

    //insert the suggestions container as a child in the search field
    searchField.parentNode.appendChild(container);
    //insert all available suggestions
    suggestionsJSONArray.forEach(suggestion => {
        /*check if the item starts with the same letters as the text field value:*/
        /*create a DIV element for each matching element:*/
        suggestedItem = document.createElement("DIV");
        //make the matching letters bold
        suggestedItem.innerHTML += suggestion.postTitle;
        /*insert a input field that will hold the current array item's value:*/
        suggestedItem.innerHTML += "<input type='hidden' value='" + suggestion.postTitle + "'>";
        /*execute a function when someone clicks on the item value (DIV element):*/
        suggestedItem.addEventListener("click", function (e) {
            /*insert the value for the autocomplete text field:*/

            closeAllLists();
        });
        container.appendChild(suggestedItem);

    });


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
}



