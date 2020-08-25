<?php
/*
*
DROP TABLE IF EXISTS errors;
CREATE TABLE `errors` (
  `date` DATE NOT NULL,  
  `number` INT(11) NOT NULL,
  `message` VARCHAR(255) NOT NULL,  
  `file` VARCHAR(255) NOT NULL,    
  `line`  VARCHAR(15) NOT NULL,
  `vars` TEXT NOT NULL,  
  `uri` VARCHAR(255) NOT NULL,  
  `host` VARCHAR(75) NOT NULL,
  `count` INT(11) NOT NULL
) ENGINE=InnoDB;
ALTER TABLE `errors` ADD UNIQUE (`date` ,`number` ,`message` ,`file` ,`line` ,`uri`);
*
*/
if(!isset($_SERVER['IS_DEVEL'])){
    function nui_error_handler($number, $message, $file, $line, $vars)  {   
      
		$skipBots = array('bingbot','acoon','baiduspider','ezooms');
		foreach($skipBots as $bot){
			if(strpos($bot,strtolower($_SERVER['HTTP_USER_AGENT']))){
				header("Status: 404 Not Found"); 
				exit();
			}	  
		}
	  
        // Alle errors erger dan Notices afhandelen      
        if ( ($number !== E_NOTICE) && ($number < 2048) ) {  
			$sql = sprintf('INSERT INTO errors 
								(`date` ,`number` ,`message` ,`file`,`line` ,`uri`,`host`,`vars`,`count`) 
							VALUES
								(DATE(NOW()),%d,"%s","%s","%s","%s","%s",\'%s\',1)
							ON DUPLICATE KEY UPDATE 
								`count`=`count`+1;',$number, quote($message), quote($file), quote($line), quote($_SERVER['REQUEST_URI']),$_SERVER['HTTP_HOST'],quote(serialize($_POST)));
			query($sql,__METHOD__);
			#echo nl2br($sql).'<br><br>';
			$sql = sprintf('SELECT `count` FROM errors WHERE 
								`date` = DATE(NOW()) 
								AND `number`=%d					
								AND	`message`="%s"
								AND `file`="%s"
								AND `line`="%s"
								AND `uri`="%s"',$number, quote($message), quote($file), quote($line), $_SERVER['REQUEST_URI']);
			#echo nl2br($sql).'<br><br>';
			$error_counter = fetchVal($sql,__METHOD__);
			#exit();
			$management_ips[] = '82.157.27.68';	
            $management_ips[] = '85.151.185.77';
			if($error_counter==1 || $error_counter==100 || in_array($_SERVER['REMOTE_ADDR'],$management_ips)){
				$email = " 
					<p>An error ($number) occurred on line 
					<strong>$line</strong> and in the <strong>file: $file.</strong> 
					<p> $message </p>";  
			  
				$email .= "<pre>" . print_r($vars, 1) . "</pre>";
				$email .= "----------SERVER VARS------------------\n";
				$email .= "<pre>" . print_r($_SERVER, 1) . "</pre>";                  
			   
				if(in_array($_SERVER['REMOTE_ADDR'],$management_ips))
					echo nl2br($email);
				
				$headers = 'Content-type: text/html; charset=iso-8859-1' . "\r\n"; 		
				// Email the error to someone...  				
				mail('anton@nui-boutkam.nl',$message,$email,'From:errors@srv1.nuicart.nl'.PHP_EOL);
				#die("There was an error in the application. We apologize for the inconvenience.<br /> This error has been logged and reported.");      
                exit();
			}
			#header( "HTTP/1.1 301 Moved Permanently" ); 
			#header( "Location: /");				
        }  
	
    }  
    set_error_handler('nui_error_handler');
}