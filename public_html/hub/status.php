<?php

$auth = htmlspecialchars($_GET["auth"]);

//If the correct parameter has been passed
if($auth == 'FDHDh6WbqKhUo40LlBpx'){
	
	$message2 = file_get_contents('plaintext.txt');

	$to2 = 'austin@allamericansports.nl';
	$subject2 = "Voorraad update succesvol";

	$reqemail2 = "Voorraad Update AAS <info@allamericansports.nl>";

	$headers2 = 'From: Voorraad Update AAS <info@allamericansports.nl>' . "\r\n";

	mail($to2, $subject2, $message2, $headers2);

}

?>