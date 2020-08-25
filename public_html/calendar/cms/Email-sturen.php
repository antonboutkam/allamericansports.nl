<?php

require_once('../class.phpmailer.php');

$teller=0;
$query = "SELECT * FROM `calendar_afspraken` WHERE id=$id";
$result = mysqli_query($con, $query);
while($row = $result->fetch_assoc())
//while($row = mysql_fetch_array($result))
{

if($teller==0){

$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
//$mail->IsSendmail(); // telling the class to use SendMail transport

	$name = $row['name'];	
	$controle=$id*$today[yday]*12347+1;
	$addURLS = "www.allamericansports.nl/calendar/annuleren.php?id=".$id."&t=".$row['time']."&q=".$controle."";
	$date = $row['date'];
	$time = $row['time'].':00';
	$categorie = $row['sport'];
	
	$text = 'Uw afspraak is bevestigd en staat gepland op '.$date.' om '.$time.'.';
	
	$message = '<html><body>';
	$message .= '<img src="http://www.allamericansports.nl/calendar/images/email-logo.jpg" alt="All- American Sports" />';
	$message .= '<p /><b>Adres showroom:</b><br />Antony van Dijckstraat 15<br />5143 JB Waalwijk<p />Uw bezoek aan de showroom is bevestigd door ons. Via onderstaande url kunt u de afspraak tot een dag van te voren annuleren.';
	$message .= '<p /><table rules="all" style="border-color: #666;" cellpadding="10">';
	$message .= "<tr><td><strong>Naam:</strong> </td><td>" .$name. "</td></tr>";
	$message .= "<tr><td><strong>Datum:</strong> </td><td>" .$date. "</td></tr>";
	$message .= "<tr><td><strong>Tijd:</strong> </td><td>" .$time. "</td></tr>";
	$message .= "<tr><td><strong>Sport:</strong> </td><td>" .$categorie. "</td></tr>";
	$message .= "<tr><td><strong>Annuleren:</strong> </td><td>" . strip_tags($addURLS) . "</td></tr>";

	$message .= "</table>";
	$message .= "</body></html>";

try {
  $mail->AddReplyTo('info@allamericansports.nl', 'All-American Sports');
  $mail->AddAddress($row['email'], $row['name']);
  $mail->SetFrom('info@allamericansports.nl', 'All-American Sports');
  $mail->AddReplyTo('info@allamericansports.nl', 'All-American Sports');
  $mail->Subject = 'Bevestiging afspraak All-American Sports';
  $mail->AltBody = $text; // optional - MsgHTML will create an alternate automatically
  $mail->MsgHTML($message);
  $mail->Send();
  echo "Message Sent OK</p>\n";
} catch (phpmailerException $e) {
  echo $e->errorMessage(); //Pretty error messages from PHPMailer
} catch (Exception $e) {
  echo $e->getMessage(); //Boring error messages from anything else!
}

$teller++;

}
}

?>