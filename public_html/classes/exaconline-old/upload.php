<?php
if($_SERVER['argv'][1]!='dev' && $_SERVER['argv'][1]!='live')
	exit('Please specify "live" or "dev" argument'.PHP_EOL);

require_once('functions.php');
require_once('../public_html/classes/exactonline/exactxml.class.inc.php');
require_once('../public_html/classes/exactonline/exactupload.class.inc.php');

$config     =   getEnvVars($_SERVER['argv'][1]);
$db_cons  	=   dbConnect($config);


$currentPage = 1;
$itemsPP = 10;

while($xml = ExactXml::make($currentPage,$itemsPP)){
    $currentPage = $currentPage+1;
    ExactUpload::send($xml);    
}