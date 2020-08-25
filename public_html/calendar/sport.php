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
<td class="item-on">
<?php
include('animation.html');
?>
</td>
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

    <p />
<?php if(is_numeric($_GET['d']) AND is_numeric($_GET['m']) AND is_numeric($_GET['y']) AND is_numeric($_GET['t'])){ ?> 	
    <table class="table">
    	<tr class="rowh">
        <td>Sport:</td>
        </tr>
  		<tr class="row" onmouseover="this.style.background='#C3C3E5';this.style.cursor='pointer'"
        onmouseout="this.style.background='#F1F0FF';" onclick="window.location.href='bevestiging.php?d=<?php echo mysqli_real_escape_string($con, $_GET['d']); ?>&m=<?php echo mysqli_real_escape_string($con, $_GET['m']); ?>&y=<?php echo mysqli_real_escape_string($con, $_GET['y']); ?>&t=<?php echo mysqli_real_escape_string($con, $_GET['t']); ?>&s=1'">
    		<td>Honkbal / Softball / Baseball</td>
  		</tr>
        <tr class="row" onmouseover="this.style.background='#C3C3E5';this.style.cursor='pointer'"
        onmouseout="this.style.background='#F1F0FF';" onclick="window.location.href='bevestiging.php?d=<?php echo mysqli_real_escape_string($con, $_GET['d']); ?>&m=<?php echo mysqli_real_escape_string($con, $_GET['m']); ?>&y=<?php echo mysqli_real_escape_string($con, $_GET['y']); ?>&t=<?php echo mysqli_real_escape_string($con, $_GET['t']); ?>&s=2'">
    		<td>Turnen / Gymnastics</td>
  		</tr>
        <tr class="row" onmouseover="this.style.background='#C3C3E5';this.style.cursor='pointer'"
        onmouseout="this.style.background='#F1F0FF';" onclick="window.location.href='bevestiging.php?d=<?php echo mysqli_real_escape_string($con, $_GET['d']); ?>&m=<?php echo mysqli_real_escape_string($con, $_GET['m']); ?>&y=<?php echo mysqli_real_escape_string($con, $_GET['y']); ?>&t=<?php echo mysqli_real_escape_string($con, $_GET['t']); ?>&s=3'">
    		<td>American Football</td>
  		</tr>
		<tr class="row" onmouseover="this.style.background='#C3C3E5';this.style.cursor='pointer'"
        onmouseout="this.style.background='#F1F0FF';" onclick="window.location.href='bevestiging.php?d=<?php echo mysqli_real_escape_string($con, $_GET['d']); ?>&m=<?php echo mysqli_real_escape_string($con, $_GET['m']); ?>&y=<?php echo mysqli_real_escape_string($con, $_GET['y']); ?>&t=<?php echo mysqli_real_escape_string($con, $_GET['t']); ?>&s=5'">
    		<td>Gratis swing analyse</td>
  		</tr>
		<tr class="row" onmouseover="this.style.background='#C3C3E5';this.style.cursor='pointer'"
        onmouseout="this.style.background='#F1F0FF';" onclick="window.location.href='bevestiging.php?d=<?php echo mysqli_real_escape_string($con, $_GET['d']); ?>&m=<?php echo mysqli_real_escape_string($con, $_GET['m']); ?>&y=<?php echo mysqli_real_escape_string($con, $_GET['y']); ?>&t=<?php echo mysqli_real_escape_string($con, $_GET['t']); ?>&s=4'">
    		<td>Anders / Other</td>
  		</tr>
	</table>
 
<?php }else{?> Unexpected input <?php } ?>
 
    <?php
}



if($_GET['p']=="an"){
	echo 'pagina om te annuleren';
}

?>
<p />


</td>


<td id="right_3">
..
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