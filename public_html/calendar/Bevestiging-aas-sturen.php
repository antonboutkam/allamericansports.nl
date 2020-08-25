

<?php
$to2 = 'info@allamericansports.nl';
$subject2 = "Afspraak notificatie All-American Sports";

$reqemail2 = "All-American Sports <info@allamericansports.nl>";

$headers2 = 'From: All-American Sports <info@allamericansports.nl>' . "\r\n";

$message2 = 'Er is een bezoek aan de showroom aangevraagd via het kalender systeem.';


mail($to2, $subject2, $message2, $headers2);
?>

