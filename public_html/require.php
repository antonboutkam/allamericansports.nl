<?php
ini_set('display_errors',1);

set_include_path(join(PATH_SEPARATOR, array(
				get_include_path(),				
				SITE_ROOT,
				SITE_ROOT.'classes/',
				SITE_ROOT.'classes/psp',   				
                /* SITE_ROOT.'classes/exactonline', */
                SITE_ROOT.'classes/exact-oath',
                SITE_ROOT.'libs/mail', 
                SITE_ROOT.'libs/thumb_plugins',
				SITE_ROOT.'classes/shippingcost',
                SITE_ROOT.'libs/excelexport',
                SITE_ROOT.'libs/excelexport/PHPExcel',
                SITE_ROOT.'libs'
                				
			)));

/*
 * Autoloading the classes. 
 * Why do we use this function?
 * 
 * Many developers writing object-oriented applications create one PHP source file per-class definition. 
 * One of the biggest annoyances is having to write a long list of needed includes at the beginning of each 
 * script (one for each class).
 * 
 * In PHP 5, this is no longer necessary. You may define an __autoload function which is automatically called 
 * in case you are trying to use a class which hasn't been defined yet. By calling this function the scripting 
 * engine is given a last chance to load the class before PHP fails with an error.  
 */			
spl_autoload_register(function($class) {/* phpunit werkt niet met GLOBAL vars $_SERVER['IS_DEVEL'] is dan ook niet aanwezig */
	$lower = strtolower($class);
	$names = array(  //lekker streng, conventie is conventie.
					"$lower.class.inc.php",
                    "$lower.class.inc.php",
					"$lower.interface.inc.php",		
				);
	# Be abit more quiet when loading non-existent files
	$prev = error_reporting(E_ALL ^ E_WARNING);
	foreach($names as $name) {	        
        if(!strpos(' '.$name,'phpexcel')){ //phpexel has it's own autoloader

            if ($res = include_once($name)){
                break;
            }
        }
    }

	# As you were.. \o_
	error_reporting($prev);
});

