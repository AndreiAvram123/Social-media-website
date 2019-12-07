<?php
//generate a random captcha in order to check if the user is a robot or not
session_start();
//generate a random number
$random_alpha = md5(rand());
$captcha_code = substr($random_alpha, 0, 6);
$target_layer = imagecreatetruecolor(100, 40);
$captcha_background = imagecolorallocate($target_layer, 135, 117, 117);
//fill the image background
imagefill($target_layer, 0, 0, $captcha_background);
//create the text to be stored in the image
$captcha_text_color = imagecolorallocate($target_layer, 0, 0, 0);
//put the string in the image
imagestring($target_layer, 5, 5, 5, $captcha_code, $captcha_text_color);
//declare this php file as being an image
header("Content-type: image/jpeg");
imagejpeg($target_layer);
imagedestroy($target_layer);
//store the captcha code in the session
$_SESSION['captcha_code'] = $captcha_code;
?>