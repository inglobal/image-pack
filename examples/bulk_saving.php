<?php

require "../Image.php";

$image = new Image;

$image->SourceDirectory = 'images/';
$image->ImageName = 'i.jpg';
$image->TargetDirectory = 'uploads/';

$imageCopies = array(
    array("postfix" => "big", "width" => 900),
    array("postfix" => "medium", "width" => 400),
    array("postfix" => "thumb", "width" => 100)
);

foreach ($imageCopies as $copy) {
    $image->setCopyName($copy['postfix']);
    if ($image->checkInit()) {
        $image->resize($copy['width']);
        $image->save();
        echo "Image {$image->TargetDirectory}{$image->CopyName} has been saved.<br>";
    } else {
        print_r($image->Errors);
    }
}