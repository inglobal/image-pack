<?php
/* 
* Outputs transformed image to the browser.
 */
require "../Image.php";

// Specify $display parameter to prevent checking of CopyName
$image = new Image($display = true);

$image->SourceDirectory = 'images/';
$image->ImageName = 'i.jpg';
if ($image->checkInit()) {
    $image->cutSquare(600, 600);
    $image->resize(100, 100);
    $image->display();
} else {
    print_r($image->Errors);
}