<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: text/plain");

    /* 
        Upload an image 
    */
    require_once "../qqFileUploader.php";

    $uploader = new qqFileUploader();

    // Specify the list of valid extensions, ex. array("jpeg", "xml", "bmp")
    $uploader->allowedExtensions = array('jpg', 'jpeg', 'gif', 'png');

    // Specify max file size in bytes.
    $uploader->sizeLimit = 2 * 1024 * 1024;

    // Specify the input name set in the javascript.
    $uploader->inputName = 'image';

    // Define upload directory.
    $uploadDirectory = 'uploads/';

    $filename_parts = explode('.', $uploader->getName());
    $filename = md5(microtime() . uniqid(mt_rand(), true)) . '.' . end($filename_parts);

    // Call handleUpload() with the name of the folder, relative to PHP's getcwd()
    $result = $uploader->handleUpload($uploadDirectory, $filename);

    if (isset($result["error"])) {
        echo json_encode($result);
        exit;
    }

    /* 
        Probably insert a record into db
    */

    echo json_encode(array("success" => true));
    exit;
}
?>
<h3>Image uploading form:</h3>
<form method="post" enctype="multipart/form-data">
<input type="file" name="image" />
<input type="submit" value="upload" />
</form>