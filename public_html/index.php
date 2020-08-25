<?php
// exit('We are down for maintenance, we will be back in 5 minutes.');
$stayHere   = false;

$whiteList = array('84.24.180.169','83.128.206.102','66.249.81.60','83.128.96.158','82.157.27.68','110.172.175.97','62.140.132.60','122.180.104.46','180.211.110.194', '127.0.0.1');
if(isset($_SERVER['IS_DEVEL']) && strpos($_SERVER['REMOTE_ADDR'],'192.168')!==0 
        && !in_array($_SERVER['REMOTE_ADDR'],$whiteList)){
    echo "<h1 style='text-align:center;'>U (".$_SERVER['REMOTE_ADDR'].") heeft geen toegang tot deze pagina</h1>";
    exit();
}


if(!preg_match('/^backoffice\./',$_SERVER['SERVER_NAME'])){

    if(!$_SERVER['IS_DEVEL']){
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
            // SSL connection
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);         
            exit(); 
        }   
    }
}

if(strpos($_SERVER['HTTP_HOST'],'backoffice')===false){
    if($_SERVER['REQUEST_URI'] == '/gb'){
      header ('HTTP/1.1 301 Moved Permanently');
      header ('Location: /gb/');
      exit();
    }
    if($_SERVER['REQUEST_URI'] == '/nl'){
      header ('HTTP/1.1 301 Moved Permanently');
      header ('Location: /nl/');
      exit();
    }
}else{
    // In de backoffice de landcode weghalen, dit is om typo's te voorkomen
    if($_SERVER['REQUEST_URI'] == '/nl/' || $_SERVER['REQUEST_URI'] == '/gb/'){
        header ('HTTP/1.1 301 Moved Permanently');
        header ('Location: /');
        exit();
    }
}

// require_once('error_handler.php');
/*
if(!isset($_SERVER['IS_DEVEL']))
{
    require_once('session.php');
}
*/


define('SITE_ROOT',dirname($_SERVER['SCRIPT_FILENAME']).'/');

if(isset($_SERVER['IS_DEVEL']))
{
    ini_set('display_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE); //  & ~E_NOTICE
}
else if(strpos($_SERVER['REMOTE_ADDR'], '83.128') === 0) //caiw.nl
{
    ini_set('display_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE);
}
else
{
    ini_set('display_errors', 0);
    error_reporting(E_NONE);
}

ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE); //  & ~E_NOTICE

ini_set('log_errors', 1);

$logdir = ($_SERVER['IS_DEVEL'])?'./log/':'../../log/';

if(!is_dir($logdir)){
    trigger_error(__METHOD__.' logdir is missing or requires write access.',E_USER_WARNING);
}



if(!isset($_GET['session'])&&!isset($_POST['session'])){
    session_start();
}

$_SESSION['debug']['loaded_templatefiles'] = null;
require_once("utils.php");
require_once("require.php");
require_once("config.php");

/* Oude url's redirecten + afvangen */
if(preg_match('#/?(.+)?/products/show/category/([0-9]+)/.+#', $_SERVER['REQUEST_URI'],$matches)){
    RedirectEngine::categoryPage();
}else if(preg_match('#/?(.+)?/products/details/([0-9]+)/.+#', $_SERVER['REQUEST_URI'],$matches)){    
    #/en/products/details/5534/ArcTeryx-Atom-LT-Hoody-Women-Azulene
    #/products/details/5534/ArcTeryx-Atom-LT-Hoody-Women-Azulene
    RedirectEngine::productPage($matches[2]);
    exit('productpage');    
}else if(preg_match('#/?(.+)?/products/show/brand/([0-9]+)/(.+)#', $_SERVER['REQUEST_URI'],$matches)){
    pre_r($matches);
    exit();
}
/* Indien er per ongeluk ergens geen url is doorgekomen, dan redirect naar zelfde url met taal */
if(!preg_match('/backoffice/',$_SERVER['HTTP_HOST']) && !isset($params['ajax']) && $_SERVER['REQUEST_URI'] != '/ajax.php'){
	if($_SERVER['REQUEST_URI']=='/'){	
	  header ('HTTP/1.1 301 Moved Permanently');
	  header ('Location: /nl/');	
	}
	
#	exit($_SERVER['REQUEST_URI']);
	if($_SERVER['REQUEST_URI']!='/feed/kieskeuriggen.xml'){    
    	if(!strpos(' '.$_SERVER['REQUEST_URI'],'/nl/') && !strpos(' '.$_SERVER['REQUEST_URI'],'/en/') && !strpos(' '.$_SERVER['REQUEST_URI'],'/gb/')){
    		#exit('/nl'.$_SERVER['REQUEST_URI']);
    		redirect('/nl'.$_SERVER['REQUEST_URI']);	
    	}
    }
	
}

if(isset($_SERVER['IS_DEVEL']))
{
    query("set global sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';", __METHOD__);
    query("set session sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';", __METHOD__);
}
$html = Page::run(array_merge($_GET,$_POST));

/*
$search = array(
    '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
    '/[^\S ]+\</s',  // strip whitespaces before tags, except space
    '/(\s)+/s'       // shorten multiple whitespace sequences
);

$replace = array(
    '>',
    '<',
    '\\1'
);

$html = preg_replace($search, $replace, $html);
*/
echo $html;
if(!isset($_SERVER['IS_DEVEL']))
{
    // sessionClean();
}
//echo "<br><h1>Query count was ".$_SESSION['query_counter']."</h1><br>";
$_SESSION['query_counter'] = 0;
