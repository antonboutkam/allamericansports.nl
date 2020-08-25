
<?php
$to = "info@allamericansports.nl";
$subject = "Annulering afspraak All-American Sports";

$reqemail = "All-American Sports <info@allamericansports.nl>";

$headers = "From: " .$reqemail. "\r\n";
$headers .= "Reply-To: ". $reqemail . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

$message = '<html><body>';
$message .= '<img src="http://www.allamericanbaseball.nl/calendar/images/email-logo.jpg" alt="All- American Sports" />';
$message .= '<p />';
$message .= $name;
$message .= ' heeft zijn/haar afspraak van <b />';
$message .= date('l', $date).' ';
$message .= $date;
$message .= ", ";
$message .= $time.':00</b> ';
$message .= 'geannuleerd.';

$message .= "</table>";
$message .= "</body></html>";

mail($to, $subject, $message, $headers);
?>
