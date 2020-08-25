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

<div id="header">

</div>

<?php include('connect.php'); ?>

<div id="menu">
<table class="menu">
<tr>
<td class="item-on">
<div style="position:relative;top:-2px;left:2px;">
<?php
include('animation.html');
?>
</div>
</td>
<td class="item">
</td>
<td class="item"></td>
<td class="item"></td>
</tr>
</table>
</div>


<div id="content">

<table id="split">
<tr>

<td id="left">
<?php

if($_GET['p']=="st" || $_GET['p']==""){
	?>

<div class="text">    
Welkom!<br /> U kunt hier online een afspraak maken om de showroom van All-American Sports te bezoeken.<p />

<p />
<br />

    <div id="calback">
		<div id="calendar"></div>
	</div>

<br />
<p />
(Beschikbare dagen worden in groen weergegeven. / Available days are shown in green.)
    
    <p />
    
    
    <?php
}

?>
<p />

</td>


<td id="right_1">

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