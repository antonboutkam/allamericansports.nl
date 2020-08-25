<?php

class Bol_update
{

	public function update_stock_bol($update_stock_xml)
		{

		date_default_timezone_set('GMT');

		$X_BOL_DATE_HEADER = "X-BOL-Date";

		$public_key = 'wiQQNcZiUKzLWAzfsyPpmMmpPyrXMmoO';
		$private_key = 'snKPLdexoBxzoLgxnKzbHEmMhHoZPOxMyKaidedhAMpaYSnDJqyTrbaavyparcKmLXhSqJeoWoCfXZQqymtGpYntIPIVGnTLkWtOcSrgeAzOArRtliggAxgUnqnxxyzHLvuAsIWdntVpPEnuypBGlzgYNgLZQrnqwtPszoXYcCWPBTOmgpDiyfgYcGZIzKTjnkiPsKTbsYWmTvLtWPIPXTPYAoBzyqIPmFeudiNYNaVRarSyVhYXeiHnZkOrAbqe';

		$method			= 'PUT';
		$dateString		= date('D\, d M Y H:i:s T');
		$contentType	= 'application/xml; charset=UTF-8';
		$path 			= '/offers/v2/'; 

		//---------------------------------------------
		//Create the signature string for authentication
		//---------------------------------------------

		$result = $method 	. "\xA\xA"
							. $contentType . "\xA"
							. $dateString . "\xA"
							. strtolower($X_BOL_DATE_HEADER) . ':' . $dateString . "\xA"
							. $path;

		$signature = base64_encode(hash_hmac('sha256', $result, $private_key, true));


		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://plazaapi.bol.com/offers/v2/",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "PUT",
		  CURLOPT_POSTFIELDS => $update_stock_xml,
		  CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"content-type: application/xml; charset=UTF-8",
			"postman-token: 72c50736-e649-4d65-0545-64fead8d7141",
			"x-bol-authorization: " . $public_key . ":" . $signature,
			"x-bol-date: " . $dateString
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		
		curl_close($curl);

		if ($err) {
		  $response = "Error: " . $err;
		} else {
		  $response = 'Voorraad is geupdated in bol.com';
		}
		

		
		return $response;
	}
}
?>