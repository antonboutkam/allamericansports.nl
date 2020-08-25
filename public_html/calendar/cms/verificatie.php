<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>All-American Sports Calendar</title>
<link rel="stylesheet" href="style.css" type="text/css" />
<link rel='stylesheet' type='text/css' href='calendar_style.css' />

</head>

<body onload="timer=setTimeout('move()',2000)">

<div id="header">
</div>

<?php include('connect.php'); ?>

<div id="menu">
<table class="menu">
<tr>
<td class="item"><a href="index.php?j=<?php $today = getdate(); echo 7*$today[yday]+1; ?>">Kalender weergave</a></td>
<td class="item-done"><a href="afspraken.php?j=<?php $today = getdate(); echo 7*$today[yday]+1; ?>">Afsprakenlijst</a></td>
<td class="item"></td>
<td class="item"></td>
</tr>
</table>
</div>

<div id="content">

<?php

if($_GET['ok']==1)
{
?>

<script language="JavaScript">
<!--
var time = 10
function move() {
window.location = 'afspraken.php?j=<?php $today = getdate(); echo 7*$today[yday]+1; ?>'
}
//-->
</script>

<?php

$id=$_GET['id'];

include('connect.php');

$sql = "UPDATE `calendar_afspraken` SET `check`='1' WHERE `id`='".$_GET['id']."'";
mysqli_query($con, $sql)or die('Error');

if($_GET['ct']%31==1){
	include('Email-sturen.php');
}

?>

<?php
}
else{
	echo '<div class="text">';
	echo '<b>Weet je zeker dat je deze afspraak wilt bevestigen?</b><p />';	
	echo '<a href="verificatie.php?ct='.$_GET['ct'].'&tijd='.$_GET['time'].'&id='.$_GET['id'].'&ok=1">Ja, bevestig</a><p /> ';
	echo '<a href="javascript: history.go(-1)">Nee</a>';
	echo '</div>';
}
?>

</div>

</body>
</html>