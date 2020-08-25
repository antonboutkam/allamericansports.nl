<?php
function config(){
    $custom = Cfg::getCustomRoot();     
    if($_SERVER['IS_DEVEL'] == true){
            $array = array(
            'LOGGING'               =>  true,
            'DB_HOST'               =>  $_SERVER['DB_SERVER'],
            'DB_USER'               =>  $_SERVER['DB_USER'],
            'DB_PASS'               =>  $_SERVER['DB_PASS'],
            'DB_NAME'               =>  'allamericansports',
            'DB_NAME_TEST'          =>  'allamericansports',
            'EXACT_CLIENT_ID'       =>  '0927ee45-ccde-4370-bd8c-efaa50a8c88c',
            'EXACT_CLIENT_SECRET'   =>  '1LjpuWjCUwXb',
            'EXACT_CLIENT_COUNTRY'  =>  'nl',
            'EXACT_DIVISION'        =>  514415,
            'DISP_QUERY'            =>  false);                                                                                                                
    }else{
            $array = array(
            'LOGGING'               =>  false,
            'DB_HOST'               =>  'localhost',
            'DB_USER'               =>  'allamericansports',
            'DB_PASS'               =>  'Xr1GVduKSe15cbOzeSn5I',
            'DB_NAME'               =>  'allamericansports',
            'DB_NAME_TEST'          =>  'allamericansports',
            'EXACT_CLIENT_ID'       =>  '1801dba6-cfdc-4a01-9381-9659c355e7ea',
            'EXACT_CLIENT_SECRET'   =>  'EGaZ76BQhzah',
            'EXACT_CLIENT_COUNTRY'  =>  'nl',
            'EXACT_DIVISION'        =>  325848,
            'DISP_QUERY'            =>  false);                                      	
    }           
    $array['PSP']                   = 'Multisafepay';
    $array['MULTISAFEPAY_API_KEY']  = 'eaef0b89ab382f969dd60721f88bd20484f8be2b';
    $array['PSP_TEST_MODE']         = ($_SERVER['IS_DEVEL'] )?true:false;           
    $array['items_pp']              = 20;
    $array['MYSQL_LOCALE']          = 'nl_NL';    
    $array['ERROR_MAILER']          = 'nuicarterrors@gmail.com';
    $array['TEMPLATE_DIRS']         = array(SITE_ROOT.'templates/');
	return $array;
}
Cfg::set(config());

 
