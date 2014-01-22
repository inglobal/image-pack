<?php
/* 
* Image - PHP image processing class.
* It can be used to transform image (resize, cutSquare methods) 
* and save it to a server or display to a browser.
*/
class Image {

    /* 
    * Name of the source image.
    */
    public $ImageName = '';

    /* 
    * Filename to which transformed image will be saved.
    */
    public $CopyName = '';

    /* 
    */
    public $SourceDirectory = '';
    
    /* 
    * 
    */
    public $TargetDirectory = '';

    /* 
    * Transformed image.
    * @var image resource gd
    */
    private $Image = NULL;

    /* 
    * Holds extension of the source image.
    * @var string
    */
    private $Extension = NULL;

    /* 
    * List of valid extensions.
    * @var string[]
    */
    private $ValidExtensions = array('jpg', 'jpeg', 'png', 'gif');

    /* 
    * If we use this class in display mode there's no
    * need in checking $CopyName.
    */
    private $DisplayMode;

    /* 
    * Error messages.
    * @var string[]
    */
    public $Errors = array();

    /* 
    */
    public function __construct($display = false) {
        $this->DisplayMode = $display;
        // ?
    }

    /* 
    */
    public function __destruct() {
        if ($this->Image !== NULL) {
            imagedestroy($this->Image);
        }
        // ?
    }

    /* 
    * Check if everything is ready to transform the image.
    * @return boolean
    */
    public function checkInit() {

        $this->Errors = array();

        // At this moment target directory may not be set.
        $this->setTargetDirectory();

        if (! $this->SourceDirectory) {
            $this->Errors[] = "The source directory hasn't been specified.";
        } elseif (is_file($this->SourceDirectory . $this->ImageName) === false) {
            $this->Errors[] = "There's no such source file {$this->ImageName}.";
        } elseif($this->checkExtension() === false) {
            $this->Errors[] = "Invalid extension {$this->getExtension()}.";
        } elseif(is_writable($this->TargetDirectory) === false) {
            $this->Errors[] = "Target directory isn't writable.";
        } else {
            // so far everything is ok
        }

        if ($this->Image === NULL) {
            if ($this->createImageFrom() === false) {
                $this->Errors[] = "Invalid image file.";
            }
        }

        // If we're in displaying mode there's no need in checking copy name
        if ($this->DisplayMode === false 
                && $this->checkCopyName() === false) {
            $this->Errors[] = "Invalid copy name {$this->CopyName}.";
        }

        return count($this->Errors) === 0;
    }

    /*
    * Set a copy name so that there isn't any file with the same 
    * name in the target directory. It prevents overwriting
    * existing images. This method is poorly written but it fits 
    * small short-lived projects.
    * @return void
    */
    public function setCopyName($postfix = 'copy') {
        $MAX_ATTEMPS = 100;
        $attemptCount = 0;
        $copyName = '';
        $extension = $this->getExtension();

        // At this moment target directory may not be set.
        $this->setTargetDirectory();

        do {
            $copyName = preg_replace(
                '/\.'. $extension .'$/', 
                '_' . $postfix . '.' . $extension, 
                $this->ImageName
            );
            $postfix .= (string) rand(0, 20);
            $attemptCount++;
        } while (is_file($this->TargetDirectory . $copyName) === true 
            && $attemptCount < $MAX_ATTEMPS);
            
        $this->CopyName = $copyName;
    }

    /*
    * Check if copy name is a valid name 
    * and a file with such name does not already exist.
    * @return boolean
    */
    private function checkCopyName() {
        return preg_match('/\.'. $this->getExtension() .'$/', $this->CopyName)
            && is_file($this->TargetDirectory . $this->CopyName) === false;
    }

    /* 
    * If target directory hasn't been specified, 
    * set it to be equal to source directory.
    * @return void
    */
    private function setTargetDirectory() {
        if (! $this->TargetDirectory) {
            $this->TargetDirectory = $this->SourceDirectory;
        }
    }

    /* 
    * Extract extension from the image.
    * return string
    */
    private function getExtension() {
        $nameParts = explode('.', $this->ImageName);
        return end($nameParts);
    }

    /* 
    * Check extension of the image.
    * Note: extension with all capitals (i.e. JPG , PNG) is also a valid extension.
    * @return boolean
    */
    private function checkExtension() {
        return in_array(strtolower($this->getExtension()), 
                $this->ValidExtensions);
    }

    /* 
    */
    private function createImageFrom() {

        $extension = $this->getExtension();
        $filename = $this->SourceDirectory . $this->ImageName;

        switch (strtolower($extension)) {
            case 'jpg':
                $imageResource = @imagecreatefromjpeg($filename);
                break;
            case 'jpeg':
                $imageResource = @imagecreatefromjpeg($filename);
                break;
            case 'gif':
                $imageResource = @imagecreatefromgif($filename);
                break;
            case 'png':
                $imageResource = @imagecreatefrompng($filename);
                @imagealphablending($imageResource, false);
                @imagesavealpha($imageResource, true);
                break;
            default:
                $imageResource = false;
                break;
        }

        // move to checkInit?
        if ($imageResource) {
            $this->Image = $imageResource;
            $this->Extension = $extension;
        }

        return $imageResource;
    }
    
    /* 
    */
    private function _imageCreateTrueColor($width, $height) {
        $image = imagecreatetruecolor($width, $height);
        
        if (strtolower($this->getExtension()) === 'png') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
            $transparentColor = imagecolorallocatealpha($image, 0, 0, 0, 127);
            imagefill($image, 0, 0, $transparentColor);
        }
        
        return $image;
    }

    /* 
    * Resize the image: zoom in or zoom out.
    * @return boolean
    */
    public function resize($width = 0, $height = 0) {

        if ($this->checkInit() === false) {
            return false;
        }

        $sourceWidth = $targetWidth = imagesx($this->Image);
        $sourceHeight = $targetHeight = imagesy($this->Image);

        if ($width > 0 && $height > 0) {

            // Resize by the shortest side.
            if ($sourceWidth < $sourceHeight) {
                $targetWidth = $width;
                $targetHeight = floor($targetHeight * ($width / $sourceWidth));
            } else {
                $targetHeight = $height;
                $targetWidth = floor($targetWidth * ($height / $sourceHeight));
            }

        // Width specified
        } elseif ($width > 0) {
            $targetWidth = $width;
            $targetHeight = floor($targetHeight * ($width / $sourceWidth));

        // Height specified
        } elseif ($height > 0) {
            $targetHeight = $height;
            $targetWidth = floor($targetWidth * ($height / $sourceHeight));

        // No resizing.
        } else {
            return false;
        }

        $targetImage = $this->_imageCreateTrueColor($targetWidth, $targetHeight);
        if (! imagecopyresampled($targetImage, $this->Image, 0, 0, 0, 0, 
                $targetWidth, $targetHeight, $sourceWidth, $sourceHeight)) {
            return false;
        }

        $this->Image = $targetImage;
        return true;
    }

    /* 
    * Cut square in the center of the Image, store it in $this->Image.
    * @return boolean
    */
    public function cutSquare($width = 90, $height = 90) {

        if ($this->checkInit() === false) {
            return false;
        }

        $sourceWidth = imagesx($this->Image);
        $sourceHeight = imagesy($this->Image);

        if ($sourceWidth < $width && $sourceHeight < $height) {
            return false;
        }

         $targetImage = $this->_imageCreateTrueColor($width, $height);

         $sourceX = floor(($sourceWidth - $width) / 2);
         $sourceY = floor(($sourceHeight - $height) / 2);

         if (! imagecopyresampled($targetImage, $this->Image, 0, 0, $sourceX, $sourceY, 
                $width, $height, $width, $height)) {
            return false;
        }

        $this->Image = $targetImage;
        return true;
    }

    /* 
    */
    public function save() {

        if ($this->checkInit() === false) {
            return false;
        }

        switch (strtolower($this->Extension)) {
            case 'jpg':
                $saved = imagejpeg($this->Image, 
                    $this->TargetDirectory . $this->CopyName, 100);
                break;
            case 'jpeg':
                $saved = imagejpeg($this->Image, 
                    $this->TargetDirectory . $this->CopyName, 100);
                break;
            case 'gif':
                $saved = imagegif($this->Image, 
                    $this->TargetDirectory . $this->CopyName);
                break;
            case 'png':
                $saved = imagepng($this->Image, 
                    $this->TargetDirectory . $this->CopyName, 9);
                break;
            default:
                $saved = false;
                break;
        }

        return $saved;
    }

    /* 
    * Output the image to the browser.
    * @return boolean
    */
    public function display() {

        if ($this->checkInit(false) === false) {
            return false;
        }

        $extension = strtolower($this->Extension);

        header('Content-type: image/'. $extension);
        switch ($extension) {
            case 'jpg':
                $displayed = imagejpeg($this->Image);
                break;
            case 'jpeg':
                $displayed = imagejpeg($this->Image);
                break;
            case 'gif':
                $displayed = imagegif($this->Image);
                break;
            case 'png':
                $displayed = imagepng($this->Image);
                break;
            default:
                $displayed = false;
                break;
        }

        return $displayed;
    }

}
