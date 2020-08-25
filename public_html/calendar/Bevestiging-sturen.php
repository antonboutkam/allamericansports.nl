
<?php
$to = mysqli_real_escape_string($con, $emailadres);
$subject = "Verzoek showroombezoek All-American Sports / Visit request showroom All-American Sports";

$reqemail = "All-American Sports <info@allamericansports.nl>";

$headers = 'From: All-American Sports <info@allamericansports.nl>' . "\r\n";

$message = 'Bedankt voor uw interesse in All-American Sports en het bezoeken van onze showroom. Wij zullen zo spoedig mogelijk uw afspraakverzoek bekijken en bevestigen. Hiervan ontvangt u nog een bericht. (Thank you for your interest in All-American Sports and visting our showroom. As soon as we have confirmed your request you will recieve a message by email.)';


mail($to, $subject, $message, $headers);
?>
