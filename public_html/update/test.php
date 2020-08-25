<?php
	
		date_default_timezone_set('GMT');
	
		#$file = escapeshellarg('log/update_bol_log.txt'); // for the security concious (should be everyone!)
		$file = 'log/update_bol_log.txt'; 
		$line = `tail -n 1 $file`;
		
		print($line);
		
		$date1 = date_create(date('d-m-Y H:i:s'));
		$date2 = date_create($line);
			
	if(date_diff($date1,$date2)->d == 0 AND date_diff($date1,$date2)->m == 0 AND date_diff($date1,$date2)->y == 0 AND date_diff($date1,$date2)->h == 0){
		print('Ja');
	}else{
		print('Nee');
	}
?>