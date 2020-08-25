<?php
session_start();
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>All-American Sports Calendar</title>
<link rel="stylesheet" href="style.css" type="text/css" />
<link rel='stylesheet' type='text/css' href='calendar_style.css' />
<script type='text/javascript' src="calendar.js"></script>

</head>

<body onLoad='navigate("","")'>

<?php
if(!$_GET['i'] && $_GET['j']%7!=1)
{

?>
<div style="margin-top: 100px;">
<img src="images/sleutel.jpg" style="height:90px; float: left; margin-right:50px;">
<form action="index.php?i=check" method="post">
    	<table>
        <tbody>
            <tr>
                <td>Gebruikersnaam:</td>
                <td><input td="" name="gebruikersnaam" type="text" /></td>
            </tr>
            <tr>
                <td>Wachtwoord:</td>
                <td><input type="password" style="background-color: #ffffa0" name="wachtwoord" /></td>
            </tr>
        </tbody>
    </table>
    <table>
        <p>
        <tbody>
        </tbody>
        </p>
    </table>
    <input type="submit" name="knop" value="ok" />
</form>
</div>

<?php

}
elseif($_GET['i'] == "check")
{
?>
<div style="margin:100px 0px 0px 150px">
<?php

include('connect-login.php');

$query= "SELECT `wachtwoord` FROM producten_inloggen WHERE gebruikersnaam='".$_POST['gebruikersnaam']."'";
$resultaat = mysqli_query($con, $query) OR die ("Kon geen verbinding maken met MySQL");
while($aantal = $resultaat->fetch_assoc())
//$aantal = mysql_num_rows($resultaat); 

if ($aantal == '0')
{ 
	echo "Inlognaam bestaat niet.";
	?>
    <br />
	<a href="index.php">Terug</a>
	<?php
} 

else 
{ 

// er is wel een resultaat gevonden, kijken of het passwoord uit de database overeenkomt met de ingevoerde passwoord.
//$login = mysqli_fetch_array($resultaat);
$login = $resultaat->fetch_assoc();

$ww = $_POST['wachtwoord'];

	if($ww == $login['wachtwoord']) 

	{
	
	echo "U bent succesvol ingelogd.";
	
#Deze functie genereerd u een Random paswoord van x karakters lang.
function GenRandomPassword($lenght) { 
        
    $str = "abcdefghijkmnopqrstuvwxyz0123456789"; 

    srand((double)microtime()*1000000); 
    for ($i=0; $i<$lenght; $i++) {
        $num = rand() % strlen($str); 
        $tmp = substr($str, $num, 1); 
        $pass = $pass . $tmp; 
    }     
    return $pass; 
}
#Genereer een paswoord van 8 karakters lang.
#Deze funtie kan u aanroepen met:
$password = GenRandomPassword("15");

$_SESSION['log'] = $password; // store session data
?>
<p />
<form action="index.php?i=<?php echo $password; ?>" method="post">
<input type="hidden" name="pass" value="<?php echo $password; ?>"/>
<input type="submit" name="knop" value="Klik hier om verder te gaan" />
</form>

<?php
} 

	else 
	{ 
	echo	'De combinatie van gebruikersnaam en wachtwoord is niet geldig.';
	?>
    <br />
	<a href="index.php">Terug</a>
	<?php
	}
?>
</div>
<?php

}

}
elseif($_GET['i'] == $_SESSION['log'] || $_GET['j']%7 == 1)
{
?>

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

if($_GET['p']=="st" || $_GET['p']==""){
?>

<p />

<table style="margin: 0px auto;">
<tr>
<td style="background-color:#f3a744;width:10px;"></td><td>Nog te bevestigen</td>
</tr>
<tr>
<td style="background-color:#a630a0;width:10px;"></td><td>Vaststaande afspraak</td>
</tr>
</table>

<p />

    <table class="tablesmall">
    <tr class="rowh">
    <td colspan="9">Lijst met afspraken</td>
    </tr>
    <tr style="font-weight:bold;border:1px solid #CDCDCD;"><td>Datum</td><td>Tijd</td><td>Naam</td><td>Email</td><td>Telefoon</td><td>Sport</td><td>Toelichting</td><td>Check</td><td></td></tr>
       <?php 
	   
	   $vandaag = date('Y-m-d');
	   
	$query = "SELECT * FROM `calendar_afspraken` WHERE `deleted` <> '1' ORDER BY `date` DESC";
	$result = mysqli_query($con, $query);
	while($row = $result->fetch_assoc())
	//while($row = mysql_fetch_array($result))
	{
		?>
		<tr class="rowsmall" <?php if($vandaag > $row['date']){ echo 'style="color:silver;"';} ?>>
        <td><?php echo $row['date']; ?></td><td><?php echo $row['time']; ?>:00</td><td><?php echo $row['name']; ?></td><td><?php echo $row['email']; ?></td><td><?php echo $row['tel']; ?></td><td><?php echo $row['sport']; ?></td><td style="text-align:center;"><?php if($row['comment']!=''){ echo '<img src="images/comment.png" style="height:20px;" onMouseover="ddrivetip(\''.$row['comment'].'\',\'white\', 300)" onMouseout="hideddrivetip()" />';} ?></td><td style="background-color:<?php if($row['check']==0){echo '#f3a744';}elseif($row['check']==1){echo '#a630a0';} ?>"><?php if($row['check']==0){echo '<a href="verificatie.php?ct='; $today = getdate(); echo 31*$today[yday]+1; echo '&tijd='; echo $row['time']; echo '&id='; echo $row['id']; echo '">bevestigen</a>';} ?></td><td class="delete"><a href="delete.php?id=<?php echo $row['id']; ?>">x</a></td>
        </tr>
	<?php
    }
	?>
	</table>
<p />

<?php
}

?>

</div>
<?php
}
?>

<div id="dhtmltooltip">sd</div>

<script type="text/javascript">

/***********************************************
* Cool DHTML tooltip script- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
***********************************************/

var offsetxpoint=-60 //Customize x offset of tooltip
var offsetypoint=20 //Customize y offset of tooltip
var ie=document.all
var ns6=document.getElementById && !document.all
var enabletip=false
if (ie||ns6)
var tipobj=document.all? document.all["dhtmltooltip"] : document.getElementById? document.getElementById("dhtmltooltip") : ""

function ietruebody(){
return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function ddrivetip(thetext, thecolor, thewidth){
if (ns6||ie){
if (typeof thewidth!="undefined") tipobj.style.width=thewidth+"px"
if (typeof thecolor!="undefined" && thecolor!="") tipobj.style.backgroundColor=thecolor
tipobj.innerHTML=thetext
enabletip=true
return false
}
}

function positiontip(e){
if (enabletip){
var curX=(ns6)?e.pageX : event.clientX+ietruebody().scrollLeft;
var curY=(ns6)?e.pageY : event.clientY+ietruebody().scrollTop;
//Find out how close the mouse is to the corner of the window
var rightedge=ie&&!window.opera? ietruebody().clientWidth-event.clientX-offsetxpoint : window.innerWidth-e.clientX-offsetxpoint-20
var bottomedge=ie&&!window.opera? ietruebody().clientHeight-event.clientY-offsetypoint : window.innerHeight-e.clientY-offsetypoint-20

var leftedge=(offsetxpoint<0)? offsetxpoint*(-1) : -1000

//if the horizontal distance isn't enough to accomodate the width of the context menu
if (rightedge<tipobj.offsetWidth)
//move the horizontal position of the menu to the left by it's width
tipobj.style.left=ie? ietruebody().scrollLeft+event.clientX-tipobj.offsetWidth+"px" : window.pageXOffset+e.clientX-tipobj.offsetWidth+"px"
else if (curX<leftedge)
tipobj.style.left="5px"
else
//position the horizontal position of the menu where the mouse is positioned
tipobj.style.left=curX+offsetxpoint+"px"

//same concept with the vertical position
if (bottomedge<tipobj.offsetHeight)
tipobj.style.top=ie? ietruebody().scrollTop+event.clientY-tipobj.offsetHeight-offsetypoint+"px" : window.pageYOffset+e.clientY-tipobj.offsetHeight-offsetypoint+"px"
else
tipobj.style.top=curY+offsetypoint+"px"
tipobj.style.visibility="visible"
}
}

function hideddrivetip(){
if (ns6||ie){
enabletip=false
tipobj.style.visibility="hidden"
tipobj.style.left="-1000px"
tipobj.style.backgroundColor=''
tipobj.style.width=''
}
}

document.onmousemove=positiontip

</script>


</body>
</html>