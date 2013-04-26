<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'simpletest' . DIRECTORY_SEPARATOR . 'autorun.php';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Image.php';


/* 
*
 */
class imageTest extends UnitTestCase {
    
    /* 
    * An image instance.
    * @var Image
    */
    private $Image;
    
    /* 
    */
    private $ValidImages = array(
        'bmp.jpg', 'i.gif', 'i.jpeg', 'i.jpg', 'i.png', 'i2.GIF', 
        'i2.JPG', 'i2.PNG', 'i_.jpg', 'jpg.png.gif.jpg', 
        'multi.dots..jpg', 'trailing blank .jpg', 
        'white space.jpg', 'кириллица с пробелами.jpg', 
        'пробел в конце .jpg'
    );
    
    /*
    */
    private $InvalidImages = array(
        // invalid extension
        'i.tif', 'i2.TIF', 'i.bmp', 'i.txt',
        
        // inexistent images
        '', 'inexistent.jpg', 'no.png'
    );


    /* 
    * Files created in the process of testing  which need to be deleted.
    */
    private $Trash = array();
    
    function __construct($test_name = false) {
        parent::__construct($test_name);
    }
    
    function setUp() {
        $this->Image = new Image;
        $this->Image->SourceDirectory = 'images/';
        $this->Image->TargetDirectory = 'uploads/';
    }
    
    function tearDown() {
        foreach ($this->Trash as $image) {
            if (is_file('uploads/'. $image)) {
                unlink('uploads/'. $image);
            }
        }
        $this->Trash = array();
        $this->Image = null;
    }
    
    function testInit() {
        foreach ($this->ValidImages as $image) {
            $this->Image->ImageName = $image;
            $this->Image->setCopyName();
            
            $this->assertTrue($this->Image->checkInit(), 
                json_encode($this->Image->Errors));
        }
        
        foreach ($this->InvalidImages as $image) {
            $this->Image->ImageName = $image;
            $this->Image->setCopyName();
            
            $this->assertFalse($this->Image->checkInit(), 
                json_encode($this->Image->Errors));
        }
    }
    
    function testResizing() {
        foreach ($this->ValidImages as $image) {
            $this->Image->ImageName = $image;
            $this->Image->setCopyName();
            $this->Image->checkInit();
            
            $this->assertTrue($this->Image->resize(2000, 2000), 
                json_encode($this->Image->Errors));
        }
    }
    
    function testCutting() {
        foreach ($this->ValidImages as $image) {
            $this->Image->ImageName = $image;
            $this->Image->setCopyName();
            $this->Image->checkInit();
            
            // Size of the cut is less than image size
            $this->assertTrue($this->Image->cutSquare(200, 200), 
                json_encode($this->Image->Errors));
                
            // Size of the cut is greater than image size
            $this->assertFalse($this->Image->cutSquare(2000, 2000), 
                json_encode($this->Image->Errors));
        }
    }

    function testCopyNameGeneration() {
        for ($i = 0; $i < 10; $i++) {
            $this->Image->ImageName = $this->ValidImages[0];
            $this->Image->setCopyName();
            $this->Image->checkInit();
            $this->Image->resize(20, 20);
            
            $this->assertTrue($this->Image->save(), 
                json_encode($this->Image->Errors));

            $this->Trash[] = $this->Image->CopyName;
        }
    }
    
    function testSaving() {
        foreach ($this->ValidImages as $image) {
            $this->Image->ImageName = $image;
            $this->Image->setCopyName();
            $this->Image->checkInit();
            $this->Image->resize(20, 20);
            
            $this->assertTrue($this->Image->save(), 
                json_encode($this->Image->Errors));
            
            $this->Trash[] = $this->Image->CopyName;
        }
    }
    
    function testBulkSaving() {
        $imageCopies = array(
            array("postfix" => "big", "width" => 900),
            array("postfix" => "medium", "width" => 400),
            array("postfix" => "thumb", "width" => 100)
        );
        
        foreach ($this->ValidImages as $image) {
            $this->Image->ImageName = $image;
            foreach ($imageCopies as $copy) {
                $this->Image->setCopyName($copy['postfix']);
                $this->Image->checkInit();
                $this->Image->resize($copy['width']);

                $this->assertTrue($this->Image->save(), 
                    json_encode($this->Image->Errors));
                
                $this->Trash[] = $this->Image->CopyName;
            }
        }
    }
}