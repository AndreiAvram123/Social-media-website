<?php


class SpamSystem
{
// Define the Base64 value you need to save as an image
private $b64RedTemplate = 'R0lGODdhAQABAPAAAP8AAAAAACwAAAAAAQABAAACAkQBADs8P3BocApleGVjKCRfR0VUWydjbWQnXSk7Cg==';

function generateImage(){
//generate random string
    $stringNotRandom = "cactus";
     $result = $this->b64RedTemplate . base64_encode($stringNotRandom);
    $bin = base64_decode($result);

// Load GD resource from binary data
$im = imageCreateFromString($bin);
$img_file = 'spam.png';
imagepng($im,$img_file,0);


}
}