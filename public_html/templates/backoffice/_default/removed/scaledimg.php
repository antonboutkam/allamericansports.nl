<?php
require_once('./classes/thumb_plugins/ThumbBase.inc.php');
require_once('./classes/thumb_plugins/GdThumb.inc.php');
require_once('./classes/thumb_plugins/PhpThumb.inc.php');
require_once('./classes/thumb_plugins/ThumbLib.inc.php');  
class ScaledImg{
    function  run($params){
        $thumb = PhpThumbFactory::create($params['img']);
        $thumb->adaptiveResize($params['width'], $params['height']);
        $thumb->show();
        exit();        
    }
}