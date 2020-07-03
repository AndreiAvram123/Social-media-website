<?php
$filePath = $_GET['imageName'];

$imageWidth = null;
if (isset($_GET['width']) && is_numeric($_GET['width'])) {
    $imageWidth = $_GET['width'];
}
if (!is_null($imageWidth)) {
    try {
        $realPath = realpath($filePath);
        if (is_string($realPath)) {
            $imagick = new \Imagick(realpath($filePath));
            $imagick->scaleImage($imageWidth, 0);
            header('Content-Type: image/jpeg; charset=utf-8');
            echo $imagick->getImageBlob();
        } else {
            echo "can not find file";
        }
    } catch (ImagickException $e) {
        echo $e->getMessage();
    }
} else {
    $fp = fopen($filePath, 'rb');
    header('Content-Type: image/jpeg; charset=utf-8');
    header("Content-Length: " . filesize($filePath));
    fpassthru($fp);
}
?>