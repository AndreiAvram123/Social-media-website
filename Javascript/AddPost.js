function submitPost(userID) {


    let postTitle = document.getElementById("postTitle");
    let postCategory = document.getElementById("postCategorySelector");
    let postContent = document.getElementById("postContent");
    let file = document.getElementById("fileToUpload").files[0];
    let errorElement = document.getElementById("errorAddPost");
    clearFields();

    function clearFields() {
        postTitle.className = "form-control";
        postContent.className = "form-control text-area";
    }

    function displayError(error) {
        errorElement.style.visibility = "visible";
        errorElement.innerText = error;
    }

    if (arePostsDetailsValid()) {
        pushRequest().then(() => {
            location.reload();
        }).catch(error => {
            displayError(error);
        })
    }

    function arePostsDetailsValid() {
        if (postTitle.value.trim() === "") {
            displayError("The title should not be empty");
            highlightElement(postTitle);
            return false;
        }

        if (postContent.value.trim() === "") {
            displayError("You must enter some text for your post");
            highlightElement(postContent);
            return false;
        }
        if (file === undefined) {
            displayError("You must select an image");
            return false;
        }
        return true;
    }

    function highlightElement(element) {
        element.className += " error-field";
    }

    function pushRequest() {
        return new Promise((resolve, reject) => {
            let url = "AddPost.php?" + "apiKey=" + apiKey;
            let formData = new FormData();
            formData.append("postTitle", postTitle.value);
            formData.append("postCategory", postCategory.value);
            formData.append("postContent", postContent.value);
            formData.append("imageName", file.name);
            formData.append("userID", userID);
            Promise.all([resizeImage(file, 600, 500),
                resizeImage(file, 70, 40)]).then((images) => {
                formData.append("imageData", images[0]);
                formData.append("imageResizedData", images[1]);
                fetch(url, {
                    method: 'POST',
                    body: formData,
                }).then(function (response) {
                    return response.text();
                }).then(function (data) {
                    let jsonData = JSON.parse(data);
                    if (jsonData.hasOwnProperty("warningMessage")) {
                        reject(jsonData);
                    } else {
                        resolve();
                    }
                })

            });


        });


    }
}
