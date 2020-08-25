<?php
require_once('../../classes/cfg.class.inc.php');
function makeAdapt($file,$newName,$w,$h){    
    $thumb = PhpThumbFactory::create($_GET['file']);
    $thumb->adaptiveResize($w,$h);
    $thumb->save($newName);    
    if(file_exists('../custom/'.Cfg::getCustomRoot().'/watermark.gif')){
        $watermark          = imagecreatefromgif('../custom/'.Cfg::getCustomRoot().'/watermark.gif');
    }else if(file_exists('../custom/'.Cfg::getCustomRoot().'/watermark.png')){
        $watermark          = imagecreatefrompng('../custom/'.Cfg::getCustomRoot().'/watermark.png');
    }  
    $watermark_width    = imagesx($watermark);  
    $watermark_height   = imagesy($watermark);        
    $image              = imagecreatefromjpeg($newName);  
    $size               = getimagesize($newName);          
    
    $dest_x             = 2;  
    $dest_y             = 0;  
    
    imagecopy($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height);  
    imagejpeg($image,$newName,100);  
	if(!is_resource($image)){
		trigger_error('Afbeelding is geen resource'.Cfg::getCustomRoot(),E_USER_WARNING);
	}
	if(!is_resource($watermark)){
		trigger_error('Watermark is geen resource'.Cfg::getCustomRoot(),E_USER_WARNING);
	}
    if(is_resource($image))
        imagedestroy($image);  
    
    if(is_resource($watermark))
        imagedestroy($watermark);      
}
//ini_set('display_errors',1);
//header('Content-Type: text/html');
header('Content-Type: image/jpeg');
# header("Cache-Control: private, max-age=10800, pre-check=10800");
# header("Pragma: private");
# header("Expires: " . date(DATE_RFC822,strtotime(" 2 day")));

$newName        = './cached/'.Cfg::getCustomRoot().'-'.$_GET['original'];
$originalImg    = preg_replace('/^[0-9]+x[0-9]+_/','',$_GET['file']);

if(!file_exists($_GET['file']))
    $_GET['file'] = '../no-img-available.jpg';

if(!file_exists($newName)||(filemtime($_GET['file']) >  filemtime($newName))){    
    require_once('../../classes/thumb_plugins/ThumbBase.inc.php');
    require_once('../../classes/thumb_plugins/GdThumb.inc.php');
    require_once('../../classes/thumb_plugins/PhpThumb.inc.php');
    require_once('../../classes/thumb_plugins/ThumbLib.inc.php');

    $wh = $_GET['w'].'x'.$_GET['h'];
#    if(in_array($wh,array('186x156','83x83','148x148','800x800','84x63','212x194','212x164','195x195','130x130','155x155','77x77','200x200'))){
        makeAdapt($_GET['file'], $newName, $_GET['w'], $_GET['h']);
        chmod($newName,0777);
#    }    
}
readfile($newName);
