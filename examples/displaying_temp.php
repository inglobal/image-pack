<?php
/* 
* Outputs transformed image to the browser.
 */
require "../Image.php";

// Specify $display parameter to prevent checking of CopyName
$image = new Image($display = true);

$image->SourceDirectory = 'uploads/';
$image->ImageName = 'i_copy.jpg';
if ($image->checkInit()) {
    // $image->cutSquare(600, 600);
    // $image->resize(300, 300);
    $image->display();
} else {
    print_r($image->Errors);
}