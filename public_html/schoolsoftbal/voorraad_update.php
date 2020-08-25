
<html>
    <head>
        <title>Start</title>
    </head>
    <body>
        <?php if(isset($_POST['private_key'])){ 
		
			date_default_timezone_set('GMT');

			$X_BOL_DATE_HEADER = "X-BOL-Date";

			$public_key = strip_tags($_POST['public_key']);
			$private_key = strip_tags($_POST['private_key']);
			
			$method			= 'GET';
			$dateString		= date('D\, d M Y H:i:s T');
			$contentType	= 'application/xml; charset=UTF-8';
			$path 			= '/offers/v2/export/'; 
			
			//---------------------------------------------
			//Create the signature string for authentication
			//---------------------------------------------
			
			$result = $method 	. "\xA\xA"
								. $contentType . "\xA"
								. $dateString . "\xA"
								. strtolower($X_BOL_DATE_HEADER) . ':' . $dateString . "\xA"
								. $path;
				
			$signature = base64_encode(hash_hmac('sha256', $result, $private_key, true));
			
			
			//---------------------------------------------
			//Get the url of the csv file
			//---------------------------------------------
			
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://plazaapi.bol.com/offers/v2/export/",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array(
				"accept: application/xml",
				"cache-control: no-cache",
				"charset: UTF-8",
				"content-type: application/xml; charset=UTF-8",
				"postman-token: 70f8c99c-c82b-ec23-ef36-4869ef645b01",
				"x-bol-authorization: " . $public_key . ":" . $signature,
				"x-bol-date: " . $dateString
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
				$xml=new SimpleXMLElement($response);
				$url_csv = $xml->Url[0];
			} 
			
			
			//---------------------------------------------
			//Create new signature string for authentication to download the csv file
			//---------------------------------------------
			
			$dateString		= date('D\, d M Y H:i:s T');
			$arr = explode("/", strrev($url_csv), 2);
			$path = '/offers/v2/export/' . strrev($arr[0]);
			
			$result = $method 	. "\xA\xA"
								. $contentType . "\xA"
								. $dateString . "\xA"
								. strtolower($X_BOL_DATE_HEADER) . ':' . $dateString . "\xA"
								. $path;
				
			$signature = base64_encode(hash_hmac('sha256', $result, $private_key, true));
			
			//---------------------------------------------
			//download the actual csv file
			//---------------------------------------------
			
			sleep(3);
			
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => $url_csv,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array(
				"cache-control: no-cache",
				"content-type: application/xml; charset=UTF-8",
				"postman-token: 70f8c99c-c82b-ec23-ef36-4869ef645b01",
				"x-bol-authorization: " . $public_key . ":" . $signature,
				"x-bol-date: " . $dateString
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {

				/*$xml=new SimpleXMLElement($response);
				$url_csv = $xml->Url[0];*/
				
				$lines = explode(PHP_EOL, $response);
				$producten_bol = array();
				foreach ($lines as $line) {
					$producten_bol[] = str_getcsv($line);
				}
				
				//print_r($producten_bol);

				foreach($producten_bol as $item) {
					echo $item[0] . ' - ';
					echo $item[4] . ' - ';
					echo $item[8] . '<br />';
				}

			} 
		
			
		} else { ?>
		
        <form name="login" action="" method="post">
            Public key:  <input type="text" name="public_key" value="" /><br />
            Private key:  <input type="password" name="private_key" value="" /><br />
            <input type="submit" name="submit" value="Submit" />
        </form>
		<?php } ?>
    </body>
</html>
