<?php
header("Content-type: text/css");
// header('Cache-control: public');

error_reporting(E_ALL);
ini_set('display_errors',1);
require_once '../classes/cfg.class.inc.php';

if(Cfg::getSiteType()=='webshops'){
    $host = Cfg::getCustomRoot();
}else{
    $host = 'backoffice.'.Cfg::getCustomRoot();
} 
    header('Content-type: text/css');
    // w

    function compress($buffer) {
            // remove comments
            $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
            // remove tabs, spaces, newlines, etc.
            $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
            return $buffer;
    }

$php = str_replace('.css','.php','./'.$host.'/'.basename($_SERVER['REQUEST_URI']));

if(file_exists($php)){            
    define('SITE_ROOT','../');
    error_reporting(E_ALL ^ E_NOTICE);

    ini_set('display_errors', ($_SERVER['IS_DEVEL'])?1:0);
    ini_set('log_errors', 1);    
    ini_set('error_log','./apache_error.txt');
    require_once(SITE_ROOT."utils.php");
    require_once(SITE_ROOT."require.php");
    require_once(SITE_ROOT."config.php");    
    $settings = Webshop::getWebshopSettings(Webshop::getIdByWebshop($host));       
    require_once $php;    
}else{
    readfile('./'.$host.'/'.basename(str_replace('nuidev.','',$_SERVER['REQUEST_URI'])));    
}


