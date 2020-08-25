<?php
require_once('../error_handler.php');
if(stripos($_SERVER['REQUEST_URI'],'png')){    
    header("Content-type: image/png");
}else if(stripos($_SERVER['REQUEST_URI'],'jpg')){
    header("Content-type: image/jpg");
}else if(stripos($_SERVER['REQUEST_URI'],'gif')){
    header("Content-type: image/gif");
}else if(stripos($_SERVER['REQUEST_URI'],'ico')){
    header("Content-type: image/x-icon");
}

require_once '../classes/cfg.class.inc.php';
$host = str_replace("_",".",$_SERVER['HTTP_HOST']);

if(Cfg::getSiteType()=='webshops'){
    $host = Cfg::getCustomRoot();
}else{
    $host = 'backoffice.'.Cfg::getCustomRoot();
} 

$host = str_replace('www.','',$host);
$file = './custom/'.$host.'/'.basename($_SERVER['REQUEST_URI']);
if(file_exists($file))
    readfile('./custom/'.$host.'/'.basename($_SERVER['REQUEST_URI']));
