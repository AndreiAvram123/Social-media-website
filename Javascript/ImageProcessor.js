let canvas = document.createElement("canvas");

function resizeImage(file) {

    return new Promise((resolve, reject) => {

            if (!file.type.match(/image.*/)) {
                reject("file not an image")
            }

            let fileReader = new FileReader();
            fileReader.addEventListener('load', () => {
                resizeImageWithCanvas(fileReader.result);
            });
            //check if the file has been chose
            if (file) {
                fileReader.readAsDataURL(file);
            }


            function resizeImageWithCanvas(imageData) {
                const img = new Image();
                img.src = imageData;
                img.onload = () => {
                    canvas.width = 250;
                    canvas.height = 200;
                    const context = canvas.getContext("2d");

                    context.drawImage(img, 0, 0, canvas.width, canvas.height);
                    resolve(canvas.toDataURL("image/jpeg").split(",")[1])
                }
            }
        }
    )
}


