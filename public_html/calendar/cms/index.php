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
<img src="images/key.png" style="height:90px; float: left; margin-right:50px;">
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
$aantal = mysqli_num_rows($resultaat); 

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
//$login = mysql_fetch_array($resultaat);
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

<div class="text">    
    Kies een dag om afpraken beschikbaar te maken.
</div>

<table style="margin: 0px auto;">
<tr>
<td><img src="images/groen0.png" /> deze dag heeft beschikbare tijden</td>
</tr>
<tr>
<td><img src="images/oranje0.png" /> deze dag heeft afspraken die niet bevestigd zijn</td>
</tr>
<tr>
<td><img src="images/paars0.png" /> deze dag heeft bevestigde afspraken</td>
</tr>
</table>
   
    <p />

    <div id="calback">
		<div id="calendar"></div>
	</div>
    
    <p />

<?php
}

?>

</div>
<?php
}
?>

</body>
</html>