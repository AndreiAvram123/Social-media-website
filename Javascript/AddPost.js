function submitPost(userID) {

    let postTitle = document.getElementById("postTitle").value;
    let postCategory = document.getElementById("postCategorySelector").value;
    let postContent = document.getElementById("postContent").value;
    let file = document.getElementById("fileToUpload").files[0];

    function displayError() {

    }

    if (arePostsDetailsValid()) {
        pushRequest().then(() => {
            location.reload();
        }).catch(error => {
            displayError();
        })
    }

    function arePostsDetailsValid() {
        return true;
    }

    function pushRequest() {
        return new Promise((resolve, reject) => {
            let url = "AddPost.php?" + "apiKey=" + apiKey;
            let formData = new FormData();
            formData.append("postTitle", postTitle);
            formData.append("postCategory", postCategory);
            formData.append("postContent", postContent);
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
