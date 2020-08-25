<?php

$URL = 'https://start.exactonline.nl/api/v1/325848/logistics/Items?%24select=ID%2CCode%2CStock%2CDescription';
$TOKEN = '&access_token=gAAAADIBNcGPE8plsOvnRSZ4gj-3e8v9a4qM_aKhgjKoz22TcnMJC0YvD2SA9G_qw__oPn06iDuIIXHI0uNa5RqS98UNUT9erStAk1tchqa8DPertcqARDJXYMhItotaT7-_DqKHFHUagE9Djt5YDQOu0Nt5ClJpjMiOJgjJELZc2P-kFAEAAIAAAABVO0hwKeOGDoB2yzVcAKSS4GVg_X6OaxGFwH2e6ZZS-v8HWtsd-PSpKOTmVb-u7pAbuaRcCr-Q3CzEfSJ9Lor9TAxBrm0SY6B41hUN68VMxH10MuuJKbdAMjchsqoPMMJppuc4uThtYBTQ-VnLh4YkKfwkYoycHNoOiJZ959x1sF8CZxXyl2wHmH6StSeYgkLffV_WjoBBQJeT7W3tSsyOo0XQNNFD4rtexlFpWY0hiNPM-mBwcJOcEUSl-MiY9L1F9Kn0vjQOoK4ujteIBc3j0K2RG0GvuiLFWdtSVeRsICFYEVGF7J2zj-YCXIO93cicw8iyMlgadhjnMmeyHRxG_Zn1G5qd3oUcbFzu40ZG5w';

$Exact_counts = array();

//safe to limit the number of request when something goes wrong
$i = 0;

while(strlen($URL) > 1 and $i < 100){

	$i = $i + 1;
	echo 'iteration: ' . $i . '<br />';
	
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => $URL . $TOKEN,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array(
		"cache-control: no-cache",
	  ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
  
		$xml=new SimpleXMLElement($response);
		
		$xml->registerXPathNamespace('d', 'http://schemas.microsoft.com/ado/2007/08/dataservices');
		$xml->registerXPathNamespace('m', 'http://schemas.microsoft.com/ado/2007/08/dataservices/metadata');
		
		$result = $xml->xpath('//d:Code | //d:Stock');
		
		$new_counts = array();
		$j = 0;
		
		for ($x = 0; $x < count($result)-1; $x=$x+2) {
			$new_counts[$j][0] = $result[$x][0];
			if(strlen($result[$x+1][0]) > 0){ $new_counts[$j][1] = $result[$x+1][0]; }else{ $new_counts[$j][1] = '0'; };
			$j++;
		}
		
		$Exact_counts = array_merge($Exact_counts, $new_counts);
		
		$link = $xml->link;
		
		if(count($link) == 2){
			$next = $link[1];
			//echo $next->attributes()->href;
			//echo '<p />';
			$URL = $next->attributes()->href;
		}else{
			$URL = '';
			echo 'end';
		}

	//end if no error
	}

//end while loop
}

echo '<p />';
echo 'Exact counts: ' .count($Exact_counts);
echo '<p />';
		
//print array
for ($x = 0; $x < count($Exact_counts)-1; $x++) {
	echo $Exact_counts[$x][0] . '  --  ';
	echo $Exact_counts[$x][1] . '<br />';
}


?>