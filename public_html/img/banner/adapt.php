<?php
require_once('../../libs/thumb_plugins/ThumbBase.inc.php');
require_once('../../libs/thumb_plugins/GdThumb.inc.php');
require_once('../../libs/thumb_plugins/PhpThumb.inc.php');
require_once('../../libs/thumb_plugins/ThumbLib.inc.php');
function makeAdapt($file,$newName,$w,$h){
    $thumb = PhpThumbFactory::create($_GET['file']);
    $thumb->adaptiveResize($w,$h);
    $thumb->show();	    
}
makeAdapt($_GET['file'], $newName, $_GET['w'], $_GET['h']);
