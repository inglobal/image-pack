<?php
/* 
* Save copy to the SourceDirectory. Use postfix 'mycopy'.
 */
require "../Image.php";

$image = new Image;

$image->SourceDirectory = 'images/';
$image->TargetDirectory = 'uploads/';
$image->ImageName = 'i.jpg';
$image->setCopyName();
if ($image->checkInit()) {
    $image->cutSquare(600, 600);
    $image->resize(60, 60);
    $image->save();
    echo "Image {$image->TargetDirectory}{$image->CopyName} has been saved.";
} else {
    print_r($image->Errors);
}