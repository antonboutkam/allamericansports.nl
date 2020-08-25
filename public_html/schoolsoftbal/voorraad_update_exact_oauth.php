<?php

$URL = 'https://start.exactonline.nl/api/oauth2/auth';
$CLIENT_ID = '?client_id={b81cc4de-d192-400e-bcb4-09254394c52a}';
$redirect_uri = '&redirect_uri=https://www.getpostman.com/oauth2/callback';
$response_type = '&response_type=code';
$force_login = '&force_login=0';

echo $URL . $CLIENT_ID . $redirect_uri . $response_type . $force_login;

	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => $URL . $CLIENT_ID . $redirect_uri . $response_type . $force_login,
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
  
		echo $response;
		
	}



?>