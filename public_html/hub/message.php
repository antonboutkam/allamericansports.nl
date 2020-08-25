<?php

$auth = htmlspecialchars($_GET["auth"]);

//If the correct parameter has been passed
if($auth == 'dODbSZOUBheEa2DuSmj3'){
	
	$to2 = 'austin@allamericansports.nl';
	$subject2 = "Voorraad update tool AAS heeft credentials nodig";

	$reqemail2 = "Voorraad Update AAS <info@allamericansports.nl>";

	$headers2 = 'From: Voorraad Update AAS <info@allamericansports.nl>' . "\r\n";

	$message2 = 'Voer credentials in via deze link: http://www.allamericansports.nl/hub/index.php/?auth=wM67StABDnaVtfeTUYV2';


	mail($to2, $subject2, $message2, $headers2);
	
}

?>