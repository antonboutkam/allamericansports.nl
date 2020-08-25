<?php
require_once('../../libs/thumb_plugins/ThumbBase.inc.php');
require_once('../../libs/thumb_plugins/GdThumb.inc.php');
require_once('../../libs/thumb_plugins/PhpThumb.inc.php');
require_once('../../libs/thumb_plugins/ThumbLib.inc.php');

function makeAdapt($file,$newName,$w,$h){

    $thumb      = PhpThumbFactory::create('./'.$file);        
    $thumb->resize($w,$h);
    
    $sourceimg  = $thumb->oldImage;       
    $palette    = imagecreate($w,$h);

    $palette    = imagecreatetruecolor($w, $h);
    $bgc        = imagecolorallocate($palette, 255, 255, 255);    
    imagefilledrectangle($palette, 0, 0, $w, $h, $bgc);

    
    $newW = $thumb->newDimensions['newWidth']; 
    $newH = $thumb->newDimensions['newHeight'];
         
    $newX = round(($w/2) - ($newW/2));
    $newY = round(($h/2) - ($newH/2));            
    imagecopymerge($palette,$sourceimg,$newX,$newY,0,0,$newW,$newH,100);
    /*        
    if(!preg_match('/no-img-available/',$_GET['file'])){
        $watermark          = imagecreatefromgif('./watermark.gif');  
        $watermark_width    = imagesx($watermark);  
        $watermark_height   = imagesy($watermark);  
                            
        $dest_x             = 3;  
        $dest_y             =  1;  
        imagecopymerge($palette, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, 50);                
    }
     * 
     */
    imagejpeg($palette,$newName,100); 

}
/*
function makeAdapt($file,$newName,$w,$h){
    $thumb = PhpThumbFactory::create($_GET['file']);
    $thumb->adaptiveResize($w,$h);
    $thumb->save($newName);

    if(file_exists('../watermark.gif')){
        $watermark          = imagecreatefromgif('../watermark.gif');  
        $watermark_width    = imagesx($watermark);  
        $watermark_height   = imagesy($watermark);  
        $image              = imagecreatetruecolor($watermark_width, $watermark_height);  
        $image              = imagecreatefromjpeg($newName);  
        $size               = getimagesize($newName);  
        $dest_x             = -3;  
        $dest_y             =  -1;  
        imagecopymerge($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, 100);
        imagejpeg($image,$newName,100);
        imagedestroy($image);
        imagedestroy($watermark);  
    }  
    
}
*/
function blankImage($w,$h,$txt){
	if($w < 280){
		$w = 280;	
	}
	$img 		= imagecreate($w,$h);
	$color 		= imagecolorallocate($img, 50, 50, 50);
	$txtColor	= imagecolorallocate($img, 150, 150, 150);
	imagestring($img,5,10,10,$txt,$txtColor);
	imagejpeg($img);
	exit();
}
#ini_set('display_errors',1);
#error_reporting(E_ALL);
#print_r($_GET);
header('Content-Type: image/jpeg');
$newName = './cached/'.$_GET['original'];

if(!file_exists($_GET['file']))
	blankImage($_GET['w'],$_GET['h'],'Sorry, this image is missing');

#clearstatcache(true,$_GET['file']);
#clearstatcache(true,$newName);
clearstatcache();
if(!file_exists($newName)||(filemtime($_GET['file']) >  filemtime($newName))){
    $wh = $_GET['w'].'x'.$_GET['h'];
    if(in_array($wh,array('854x616','377x308','43x43','70x70','800x800','84x63','212x194','212x164','195x195','130x130','155x155','77x77'))){
       makeAdapt($_GET['file'], $newName, $_GET['w'], $_GET['h']);
       chmod($newName,0777);
    }else{
		blankImage($_GET['w'],$_GET['h'],'Dimentions are not supported.');
	}   
}
print file_get_contents($newName);