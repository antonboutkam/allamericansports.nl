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
<td class="item-done">Annuleren</td>
<td class="item"></td>
<td class="item"></td>
<td class="item"></td>
</tr>
</table>
</div>

<div id="content">
<?php

if($_GET['do']%17==1){
	$id=$_GET['id'];
	$tijd = $_GET['t'].'u';
	
	$queryx = "SELECT * FROM `calendar_afspraken` WHERE id=$id";
	$resultx = mysqli_query($con, $queryx);
	while($rowx = $resultx->fetch_assoc())
	//while($rowx = mysql_fetch_array($resultx))
	{
		
		$datum = "'".$rowx['date']."'";
		$querys = "UPDATE `calendar_vrij` SET `$tijd`= 1 WHERE date=$datum";
		mysqli_query($con, $querys)or die('Error.');
		
		$queryw = "UPDATE `calendar_afspraken` SET `deleted` = 1 WHERE id=$id";
		mysqli_query($con, $queryw)or die('Error.');
		
		$name = $rowx['name'];
		$time = $rowx['time'];
		$date = $rowx['date'];
		
		include('Email-annuleren.php');
		
	}
	echo '<div class="text">Uw afspraak is geannuleerd. <p /> <a href="http://www.allamericansports.nl/calendar/index.php">Klik hier om direct een nieuwe afspraak te maken.</a></div><p />';
	echo '<div class="text">Your visit has been canceled. <p /> <a href="http://www.allamericansports.nl/calendar/index.php">Click here for planning a new visit.</a></div>';
}
elseif($_GET['do']%17==2){
	echo '<div class="text">Uw afspraak staat nog steeds gepland en is niet geannuleerd.<p /> <a href=\"http://www.allamericansports.nl/calendar/index.php">Naar de startpagina van het afspraaksysteem</a><p /> <a href=\"http://www.allamericansports.nl\">Naar de All-American Sports website</a></div><p />';
	echo '<div class="text">Your visit has not been canceled<p /> <a href=\"http://www.allamericansports.nl/calendar/index.php">To the homepage</a><p /> <a href=\"http://www.allamericansports.nl\">To the All-American Sports website</a></div>';
}
else{

$counter=0;
if($_GET['id'] && $_GET['t'] && $_GET['q']){
	
$id=$_GET['id'];

$timedatabase=99;

$query = "SELECT * FROM `calendar_afspraken` WHERE id=$id";
$result = mysqli_query($con, $query);
while($row = $result->fetch_assoc())
//while($row = mysql_fetch_array($result))
{
$counter++;
$timedatabase=$row['time'];

if($_GET['t']==$timedatabase && $row['check']==1){
	?>

<p />

<div style="text-align:center;font-size:16px;width:100%;">
<b><?php echo $row['date']; ?> om <?php echo $row['time'].':00'; ?></b>
</div>

<p />

    <table class="table">
    	<tr class="rowh">
        <td>Weet u zeker dat u deze afspraak wilt annuleren?</td>
        </tr>
  		<tr class="row" onmouseover="this.style.background='#C3C3E5';this.style.cursor='pointer'"
        onmouseout="this.style.background='#F1F0FF';" onclick="window.location.href='annuleren.php?do=<?php echo 17*$id+1; ?>&id=<?php echo $id; ?>&t=<?php  echo $_GET['t']; ?>&q=<?php echo $_GET['q']; ?>'">
    		<td>Ja</td>
  		</tr>
        <tr class="row" onmouseover="this.style.background='#C3C3E5';this.style.cursor='pointer'"
        onmouseout="this.style.background='#F1F0FF';" onclick="window.location.href='annuleren.php?do=<?php echo 17*$id+2; ?>&id=<?php echo $id; ?>&t=<?php  echo $_GET['t']; ?>&q=<?php echo $_GET['q']; ?>'">
    		<td>Nee</td>
  		</tr>
	</table>
    
    <p />
    
	<?php
}
else{
	?>
    <div class="text">
	Deze afspraak is al geannuleerd of de url is niet geldig.<p />
    This visit has been canceled already or the url is not valid.
	</div>
	<?php
}

}

}
else{
?>
<div class="text">
	De url is niet geldig. <p /> Probeer de gehele url uit uw email te kopieren en in de adresbalk te plakken om de afspraak te annuleren.<p />
    This url in not valid. Try to paste the url from your email to cancel your visit.
</div>

<?php
}
if($counter==0){
?>
    <div class="text">
    Deze url geeft geen afspraak aan die geannuleerd kan worden.<p ?>
    This url is not valid.
    </div>
<?php
}
}
?>

</div>

</body>
</html>