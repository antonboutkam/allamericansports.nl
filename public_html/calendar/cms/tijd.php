<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>All-American Sports Calendar</title>
<link rel="stylesheet" href="style.css" type="text/css" />
<link rel='stylesheet' type='text/css' href='calendar_style.css' />

<?php

if($_GET['p']=="subm"){
?>
<script language="JavaScript">
<!--
var time = 100
function move() {
window.location = 'tijd.php?d=<?php echo $_GET['d']; ?>&m=<?php echo $_GET['m']; ?>&y=<?php echo $_GET['y']; ?>'
}
//-->
</script>
<?php
}
?>

</head>

<body <?php if($_GET['p']=="subm"){ ?> onload="timer=setTimeout('move()',2000)" <?php } ?>>

<div id="header">
</div>

<?php include('connect.php'); 

if((is_numeric($_GET['d']) OR empty($_GET['d'])) AND (is_numeric($_GET['m']) OR empty($_GET['m'])) AND (is_numeric($_GET['y']) OR empty($_GET['y']))){
$datecounter=0;
$datetargets="'".$_GET['y']."-".$_GET['m']."-".$_GET['d']."'";
$query = "SELECT * FROM `calendar_vrij` WHERE date=$datetargets";
$result = mysqli_query($con, $query);
while($row = $result->fetch_assoc())
//while($row = mysql_fetch_array($result))
{
	$datecounter++;
} 
?>

<div id="menu">
<table class="menu">
<tr>
<td class="item-done"><a href="index.php?j=<?php $today = getdate(); echo 7*$today[yday]+1; ?>">Kalender weergave</a></td>
<td class="item"><a href="afspraken.php?j=<?php $today = getdate(); echo 7*$today[yday]+1; ?>">Afsprakenlijst</a></td>
<td class="item"></td>
<td class="item"></td>
</tr>
</table>
</div>

<div id="content">
<?php

if($_GET['p']=="st" || $_GET['p']==""){
	?>
   
    <p />

<div class="text">
Geef de beschikbare tijden aan.
</div>  

<p />
        
<div style="font-size:16px;width:400px;text-align:center;margin:0px auto;"><b><?php


    $arraymaand = array(
    "Januari",
    "Februari",
    "Maart",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Augustus",
    "September",
    "Oktober",
    "November",
    "December"
    );
    $datum = $_GET['d']." ".$arraymaand[$_GET['m'] - 1]." ".$_GET['y'];
	
	if(date("l", mktime(0, 0, 0, $_GET['m'], $_GET['d'], $_GET['y']))=="Monday"){
		$weekdag = "Maandag";
	}
	elseif(date("l", mktime(0, 0, 0, $_GET['m'], $_GET['d'], $_GET['y']))=="Tuesday"){
		$weekdag = "Dinsdag";
	}
	elseif(date("l", mktime(0, 0, 0, $_GET['m'], $_GET['d'], $_GET['y']))=="Wednesday"){
		$weekdag = "Woensdag";
	}
	elseif(date("l", mktime(0, 0, 0, $_GET['m'], $_GET['d'], $_GET['y']))=="Thursday"){
		$weekdag = "Donderdag";
	}
	elseif(date("l", mktime(0, 0, 0, $_GET['m'], $_GET['d'], $_GET['y']))=="Friday"){
		$weekdag = "Vrijdag";
	}
	elseif(date("l", mktime(0, 0, 0, $_GET['m'], $_GET['d'], $_GET['y']))=="Saturday"){
		$weekdag = "Zaterdag";
	}
	elseif(date("l", mktime(0, 0, 0, $_GET['m'], $_GET['d'], $_GET['y']))=="Sunday"){
		$weekdag = "Zondag";
	}
	
    echo $weekdag." ".$datum; 
?></b></div>
        
	</table>
    
    <p />
    
    <form method="post" action="tijd.php?dc=<?php echo $datecounter; ?>&p=subm&d=<?php echo $_GET['d']; ?>&m=<?php echo $_GET['m']; ?>&y=<?php echo $_GET['y']; ?>">
    <table class="tijden">
    <tr>
    <td>6:00</td><td>7:00</td><td>8:00</td><td>9:00</td><td>10:00</td><td>11:00</td><td>12:00</td><td>13:00</td><td>14:00</td><td>15:00</td><td>16:00</td><td>17:00</td><td>18:00</td><td>19:00</td><td>20:00</td><td>21:00</td><td>22:00</td>
    </tr>
    <tr>

    <?php
	$datetargets="'".$_GET['y']."-".$_GET['m']."-".$_GET['d']."'";
    for($i=6;$i<=22;$i++){
		
		$paarscheck=0;
		$queryx = "SELECT * FROM `calendar_afspraken` WHERE date=$datetargets AND time=$i";
		$resultx = mysqli_query($con, $queryx);
		while($rowx = $resultx->fetch_assoc())
		//while($rowx = mysql_fetch_array($resultx))
		{
			if($rowx['check']==0 && $rowx['deleted']!=1){
				$paarscheck=1;
			}
		elseif($rowx['check']==1 && $rowx['deleted']!=1){
				$paarscheck=2;
			}
		}
		
	?>
	<td <?php 
		$querys = "SELECT * FROM `calendar_vrij` WHERE date=$datetargets";
		$results = mysqli_query($con, $querys);
		while($rows = $results->fetch_assoc())
		//while($rows = mysql_fetch_array($results))
		{
			$num = $i.'u';
			if($rows[$num]==1){ echo "style='background-color:#2cec31;'"; $datecounter++; }elseif($paarscheck==1){ echo "style='background-color:#f3a744;'"; $datecounter++; }elseif($paarscheck==2){ echo "style='background-color:#a630a0;'"; $datecounter++; }
		} 
		?>><input type="checkbox" name="<?php echo $i; ?>" value="1" <?php 
		$querys = "SELECT * FROM `calendar_vrij` WHERE date=$datetargets";
		$results = mysqli_query($con, $querys);
		while($rows = $results->fetch_assoc())
		//while($rows = mysql_fetch_array($results))
		{
			$num = $i.'u';
			if($rows[$num]==1){ echo "checked"; }elseif($paarscheck==1 || $paarscheck==2){ echo "disabled"; }
		} 
		?>></td>
	<?php
	}
    ?>
    
    </tr>
    </table>

<p />

<table class="table_two">
<tr>
<td class="row" onmouseover="this.style.background='#C3C3E5';this.style.cursor='pointer'"
				onmouseout="this.style.background='#F1F0FF';" onclick="window.location.href='index.php?j=<?php $today = getdate(); echo 7*$today[yday]+1; ?>'">Naar kalender</td>
<td><INPUT TYPE="image" SRC="images/opslaan.jpg" HEIGHT="41" WIDTH="139" BORDER=0 ALT="opslaan"></td>
</tr>
</table>
</form>

<p />

<table style="margin: 0px auto;">
<tr>
<td style="background-color:#2cec31;width:10px;"></td><td>Beschikbaar tijdstip</td>
</tr>
<tr>
<td style="background-color:#f3a744;width:10px;"></td><td>Nog te bevestigen</td>
</tr>
<tr>
<td style="background-color:#a630a0;width:10px;"></td><td>Vaststaande afspraak</td>
</tr>
</table>

<p />

<?php
}
elseif($_GET['p']=="subm"){

$insert = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

	for($i=0;$i<=16;$i++){
		if($_POST[$i+6]==1){
			$insert[$i] = 1;
		}
		else{
			$insert[$i] = 0;
		}
	}
	
$datetargets="'".$_GET['y']."-".$_GET['m']."-".$_GET['d']."'";
	$datetarget = $_GET['y']."-".$_GET['m']."-".$_GET['d'];
		
	$query = "SELECT * FROM `calendar_vrij` WHERE `date`=$datetargets";
	$result = mysqli_query($con, $query);
	while($row = $result->fetch_assoc())
	//while($row = mysqli_fetch_array($result))
	{
		for($i=6;$i<=22;$i++){
			$tijdstip = $i.'u';
			if($row[$tijdstip]==2){		
				$insert[$i-6] = 2;
			}
			elseif($row[$tijdstip]==3){		
				$insert[$i-6] = 3;
			}
		}
	}
		
		
		
	if($_GET['dc']>0){
		
		$sql1 = "DELETE FROM `calendar_vrij` WHERE date=$datetargets";
		mysqli_query($con, $sql1)or die('Error, verwijderen lukt niet.');
		
		
		$sql = "INSERT INTO `calendar_vrij` (`date`,`6u`,`7u`,`8u`,`9u`,`10u`,`11u`,`12u`,`13u`,`14u`,`15u`,`16u`,`17u`,`18u`,`19u`,`20u`,`21u`,`22u`) VALUES ('".$datetarget."','".$insert[0]."','".$insert[1]."','".$insert[2]."','".$insert[3]."','".$insert[4]."','".$insert[5]."','".$insert[6]."','".$insert[7]."','".$insert[8]."','".$insert[9]."','".$insert[10]."','".$insert[11]."','".$insert[12]."','".$insert[13]."','".$insert[14]."','".$insert[15]."','".$insert[16]."')";
	mysqli_query($con, $sql)or die('Error, aanpassen lukt niet.');
	}
	else{
	$sql = "INSERT INTO `calendar_vrij` (`date`,`6u`,`7u`,`8u`,`9u`,`10u`,`11u`,`12u`,`13u`,`14u`,`15u`,`16u`,`17u`,`18u`,`19u`,`20u`,`21u`,`22u`) VALUES ('".$datetarget."','".$insert[0]."','".$insert[1]."','".$insert[2]."','".$insert[3]."','".$insert[4]."','".$insert[5]."','".$insert[6]."','".$insert[7]."','".$insert[8]."','".$insert[9]."','".$insert[10]."','".$insert[11]."','".$insert[12]."','".$insert[13]."','".$insert[14]."','".$insert[15]."','".$insert[16]."')";
	mysqli_query($con, $sql)or die('Error, toevoegen lukt niet.');
	}
	?>

<?php
}
?>
</div>

<?php }else{?> Unexpected input <?php } ?>


</body>
</html>