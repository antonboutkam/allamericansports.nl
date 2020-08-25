<?php
session_start();
include("simple-php-captcha.php");
$_SESSION['captcha'] = simple_php_captcha();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>All-American Sports Calendar</title>
<link rel="stylesheet" href="style.css" type="text/css" />
</head>

<body>

<div id="header">

</div>

<?php include('connect.php'); ?>


<div id="menu">
<table class="menu">
<tr>
<td class="item-done"></td>
<td class="item-done"></td>
<td class="item-done"></td>
<td class="item-on">
<?php
include('animation.html');
?>
</td>
</tr>
</table>
</div>


<div id="content">

<table id="split">
<tr>

<td id="left">
<?php

if($_GET['c']%13==1){
	
if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
{
	echo '<div class="text">';
	echo "Vul een juist emailadres in. / Fill in your email address<p />";
	echo '<a href="javascript:javascript:history.go(-1)">Ga terug / Return</a>';
	echo '</div>';
}
elseif($_POST['name']=='' || $_POST['tel']==''){
	echo '<div class="text">';
	echo 'Vul alle velden in om de afspraak te maken. / Fill in all required fields.<p />';
	echo '<a href="javascript:javascript:history.go(-1)">Ga terug / Return</a>';
	echo '</div>';
}
elseif($_POST['sport']=='' || $_POST['time']==''){
	echo '<div class="text">';
	echo 'Er is iets fout gegaan. Probeer opnieuw uw afspraak te maken via <a href="www.allamericansports.nl/calendar">de startpagina</a>.';
	echo 'Something went wrong. Try again via <a href="www.allamericansports.nl/calendar">the homepage</a>.';
	echo '</div>';
}
elseif($_POST['cap'] != $_POST['cap_check']){
	echo '<div class="text">';
	echo 'De verificatie code komt niet overeen.';
	echo 'The verification code is not correct.';
	echo '</div>';
}
else{

	if(is_numeric($_POST['year']) AND is_numeric($_POST['month']) AND is_numeric($_POST['day']) ){
		
		$emailadres = mysqli_real_escape_string($con, $_POST['email']);

		include('Bevestiging-sturen.php');

		include('Bevestiging-aas-sturen.php');
			
		$datetarget = mysqli_real_escape_string($con, "".$_POST['year']."-".$_POST['month']."-".$_POST['day']."");

		echo "<div class='text' style=\"width:350px;\">Uw afspraakverzoek is succesvol verzonden. Zodra wij uw afspraak hebben bevestigd ontvangt u hiervan bericht per email.<p /> <a href=\"http://www.allamericansports.nl/calendar/index.php\">Terug naar de beginpagina</a><p /> <a href=\"http://www.allamericansports.nl\">Naar de All-American Sports website</a></div><p />";
		echo "<div class='text' style=\"width:350px;\">Your visit request has been succesfully submitted. As soon as we confirm your visit you will recieve a message by email.<p /> <a href=\"http://www.allamericansports.nl/calendar/index.php\">Back to the homepage</a><p /> <a href=\"http://www.allamericansports.nl\">Back to the All-American Sports website</a></div>";
		$sql = "INSERT INTO `calendar_afspraken` (`date`, `time`, `name`, `email`, `tel`, `sport`, `comment`) VALUES ('".$datetarget."','".mysqli_real_escape_string($con, $_POST['time'])."','".mysqli_real_escape_string($con, $_POST['name'])."', '".mysqli_real_escape_string($con, $_POST['email'])."','".mysqli_real_escape_string($con, $_POST['tel'])."','".mysqli_real_escape_string($con, $_POST['sport'])."','".mysqli_real_escape_string($con, $_POST['comment'])."')";
		mysqli_query($con, $sql)or die('Error, toevoegen lukt niet.');

		$field = mysqli_real_escape_string($con, $_POST['time'].'u');
		$sqlx = "UPDATE `calendar_vrij` SET `".$field."`='2' WHERE `date`='".$datetarget."'";
		mysqli_query($con, $sqlx)or die('Error');
	}
}
}
elseif($_GET['p']=="st" || $_GET['p']==""){
	?>

<?php if(is_numeric($_GET['d']) AND is_numeric($_GET['m']) AND is_numeric($_GET['y']) AND is_numeric($_GET['t']) AND is_numeric($_GET['s'])){ ?> 

<div class="text">
Voer uw contactgegevens in zodat wij uw afspraak kunnen bevestigen.
<br/ >
<p />

<?php
if(date("l", mktime(0, 0, 0, $_GET['m'], $_GET['d'], $_GET['y']))=="Monday"){
		$weekdag = "Maandag / Monday";
	}
	elseif(date("l", mktime(0, 0, 0, $_GET['m'], $_GET['d'], $_GET['y']))=="Tuesday"){
		$weekdag = "Dinsdag / Tuesday";
	}
	elseif(date("l", mktime(0, 0, 0, $_GET['m'], $_GET['d'], $_GET['y']))=="Wednesday"){
		$weekdag = "Woensdag / Wednesday";
	}
	elseif(date("l", mktime(0, 0, 0, $_GET['m'], $_GET['d'], $_GET['y']))=="Thursday"){
		$weekdag = "Donderdag / Thursday";
	}
	elseif(date("l", mktime(0, 0, 0, $_GET['m'], $_GET['d'], $_GET['y']))=="Friday"){
		$weekdag = "Vrijdag / Friday";
	}
	elseif(date("l", mktime(0, 0, 0, $_GET['m'], $_GET['d'], $_GET['y']))=="Saturday"){
		$weekdag = "Zaterdag / Saturday";
	}
	elseif(date("l", mktime(0, 0, 0, $_GET['m'], $_GET['d'], $_GET['y']))=="Sunday"){
		$weekdag = "Zondag / Sunday";
	}

?>
<div style="padding:5px;text-align:center;font-size:16px;border:1px solid gray;"><b><?php echo $weekdag.' '; echo mysqli_real_escape_string($con, $_GET['d'])."-".mysqli_real_escape_string($con, $_GET['m'])."-".mysqli_real_escape_string($con, $_GET['y']); ?> - <?php echo mysqli_real_escape_string($con, $_GET['t']); ?>:00</b></div>
<p />
<form method="post" action="bevestiging.php?c=<?php echo rand(142, 216)*13+1; ?>">
<table>
<tr><td>Naam / Name:*</td><td><input type="text" name="name"/></td></tr>
<tr><td>Telefoon /<br /> Telephone:*</td><td><input type="text" name="tel" /></td></tr>
<tr><td>Email / Email:*</td><td><input type="text" name="email" /></td></tr>
<tr><td>Toelichting /<br /> Comment:</td><td><textarea rows="4" cols="30" name="comment"></textarea></td></tr>
<input type="hidden" name="day" value="<?php echo mysqli_real_escape_string($con, $_GET['d']); ?>" />
<input type="hidden" name="month" value="<?php echo mysqli_real_escape_string($con, $_GET['m']); ?>" />
<input type="hidden" name="year" value="<?php echo mysqli_real_escape_string($con, $_GET['y']); ?>" />
<input type="hidden" name="time" value="<?php echo mysqli_real_escape_string($con, $_GET['t']); ?>" />
<input type="hidden" name="cap_check" value="<?php echo $_SESSION['captcha']['code']; ?>" />
<input type="hidden" name="sport" value="<?php if($_GET['s']==1){echo "Honkbal";}elseif($_GET['s']==2){echo "Turnen";}elseif($_GET['s']==3){echo "American Football";}elseif($_GET['s']==5){echo "Swing analyse";}else{echo "Overig";} ?>" /> 
<tr><td></td><td><img src="<?php echo $_SESSION['captcha']['image_src']; ?>"></td></tr>
<tr><td>Verificatie / Verification:*</td><td><input type="text" name="cap" /></td></tr>

</table>


</div>  
<?php }else{?> Unexpected input <?php } ?>
  

    <p />

    <table style="margin:0px auto;">
  		<tr>
    		<td style="border-left: 1px solid black;"><INPUT TYPE="image" SRC="cms/images/opslaan.jpg" HEIGHT="41" WIDTH="139" BORDER=0 ALT="opslaan"></td>
  		</tr>
	</table>
</form>    
    <?php
}


?>
<p />


</td>


<td id="right_4">&nbsp;

</td>

</tr>
</table>


<div id="footer">
<?php
include('footer.html');
?>
</div>


 <iframe width="850" height="300" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.nl/maps?f=q&amp;source=s_q&amp;hl=nl&amp;geocode=&amp;q=Antony+van+Dijckstraat+15,+Waalwijk&amp;aq=t&amp;sll=51.673683,5.079696&amp;sspn=0.002705,0.0103&amp;ie=UTF8&amp;hq=&amp;hnear=Antony+van+Dijckstraat+15,+Waalwijk,+Noord-Brabant&amp;ll=51.673591,5.079256&amp;spn=0.002788,0.0103&amp;z=14&amp;output=embed"></iframe>
<!-- <br /><small><a href="http://maps.google.nl/maps?f=q&amp;source=embed&amp;hl=nl&amp;geocode=&amp;q=Antony+van+Dijckstraat+15,+Waalwijk&amp;aq=t&amp;sll=51.673683,5.079696&amp;sspn=0.002705,0.0103&amp;ie=UTF8&amp;hq=&amp;hnear=Antony+van+Dijckstraat+15,+Waalwijk,+Noord-Brabant&amp;ll=51.673591,5.079256&amp;spn=0.002788,0.0103&amp;z=14" style="color:#0000FF;text-align:left">Grotere kaart weergeven</a></small>-->


</div>

</body>
</html>