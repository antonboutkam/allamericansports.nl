<?

include('connect.php');

$output = '';
$month = mysqli_real_escape_string($con,$_GET['month']);
$year = mysqli_real_escape_string($con,$_GET['year']);

if((is_numeric($month) OR empty($month)) AND (is_numeric($year) OR empty($year))){
	
if($month == '' && $year == '') { 
	$time = time();
	$month = date('m',$time);
    $year = date('Y',$time);
}

$date = getdate(mktime(0,0,0,$month,1,$year));
$today = getdate();
$hours = $today['hours'];
$mins = $today['minutes'];
$secs = $today['seconds'];

if(strlen($hours)<2) $hours="0".$hours;
if(strlen($mins)<2) $mins="0".$mins;
if(strlen($secs)<2) $secs="0".$secs;

$days=date("t",mktime(0,0,0,$month,1,$year));
$start = $date['wday']+1;
$name = $date['month'];
$year2 = $date['year'];
$offset = $days + $start - 1;

if($month==12) { 
	$next=1; 
	$nexty=$year + 1; 
} else { 
	$next=$month + 1; 
	$nexty=$year; 
}

if($month==1) { 
	$prev=12; 
	$prevy=$year - 1; 
} else { 
	$prev=$month - 1; 
	$prevy=$year; 
}

if($offset <= 28) $weeks=28; 
elseif($offset > 35) $weeks = 42; 
else $weeks = 35; 

$maand = '';
if($name=="January"){
$maand = "Januari";
}
elseif($name=="February"){
$maand = "Februari";
}
elseif($name=="March"){
$maand = "Maart";
}
elseif($name=="April"){
$maand = "April";
}
elseif($name=="May"){
$maand = "Mei";
}
elseif($name=="June"){
$maand = "Juni";
}
elseif($name=="July"){
$maand = "Juli";
}
elseif($name=="August"){
$maand = "Augustus";
}
elseif($name=="September"){
$maand = "September";
}
elseif($name=="October"){
$maand = "Oktober";
}
elseif($name=="November"){
$maand = "November";
}
elseif($name=="December"){
$maand = "December";
}	

$output .= "
<table class='cal' cellspacing='1'>
<tr>
	<td colspan='7'>
		<table class='calhead'>
		<tr>
			<td>
				<a href='javascript:navigate($prev,$prevy)'><img src='calLeft.gif'></a> <a href='javascript:navigate(\"\",\"\")'><img src='calCenter.gif'></a> <a href='javascript:navigate($next,$nexty)'><img src='calRight.gif'></a>
			</td>
			<td align='right'>
				<div>$maand $year2</div>
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr class='dayhead'>
	<td>Zo</td>
	<td>Ma</td>
	<td>Di</td>
	<td>Wo</td>
	<td>Do</td>
	<td>Vr</td>
	<td>Za</td>
</tr>";

$col=1;
$cur=1;
$next=0;

for($i=1;$i<=$weeks;$i++) {
	if($next==3) $next=0;
	if($col==1) $output.="<tr class='dayrow'>";

	$output.="<td valign='top'";

$countvrij=0;
$countbev=0;
$countdef=0;
$c = $_GET['c'];
$datetarget="'".$year."-".$month."-".$cur."'";
$query = "SELECT * FROM `calendar_vrij` WHERE date=$datetarget";
$result = mysqli_query($con, $query);
while($row = $result->fetch_assoc())
//while($row = mysql_fetch_array($result))
{
if(($row['6u']==1 || $row['7u']==1 || $row['8u']==1 || $row['9u']==1 || $row['10u']==1 || $row['11u']==1 || $row['12u']==1 || $row['13u']==1 || $row['14u']==1 || $row['15u']==1 || $row['16u']==1 || $row['17u']==1 || $row['18u']==1 || $row['19u']==1 || $row['20u']==1 || $row['21u']==1 || $row['22u']==1) && $rows['deleted']!=1){
$countvrij++;
	}
}

$querys = "SELECT * FROM `calendar_afspraken` WHERE date=$datetarget";
$results = mysqli_query($con, $querys);
while($row = $results->fetch_assoc())
//while($rows = mysql_fetch_array($results))
{
	if($rows['check']==0 && $rows['deleted']!=1){
		$countbev++;	
	}
	elseif($rows['check']==1 && $rows['deleted']!=1){
		$countdef++;	
	}
}
	

	if($countvrij>0 && ($i <= ($days+($start-1)) && $i >= $start)){
		$output.="class=\"av\" onMouseOver=\"this.className='dayovera'\" onMouseOut=\"this.className='dayouta'\" onclick=\"window.location.href='tijd.php?";
		$output.="&d=";
		$output.=$cur;
		$output.="&m=";
		$output.=$month;	
		$output.="&y=";
		$output.=$year;
		$output.="'\">";
	}
	else{
		if($i <= ($days+($start-1)) && $i >= $start){
			$output.="onMouseOver=\"this.className='dayover'\" onMouseOut=\"this.className='dayout'\" onclick=\"window.location.href='tijd.php?";
		}
		$output.="&d=";
		$output.=$cur;
		$output.="&m=";
		$output.=$month;	
		$output.="&y=";
		$output.=$year;
		$output.="'\">";
	}
	
	
	if($i <= ($days+($start-1)) && $i >= $start) {
		$output.="<div class='day'><b";

		if(($cur==$today[mday]) && ($name==$today[month])) $output.=" style='color:#C00'";

		$output.=">$cur</b></div>";

		$cur++; 
		$col++;
		
		if($countvrij > 0){
			$output.="<img src=\"images/groen0.png\">";
		}
		if($countbev>0){
			$output.="<img src=\"images/oranje0.png\">";
		}
		if($countdef>0){
			$output.="<img src=\"images/paars0.png\">";
		}
		
		$output.="</td>";
	
	} else { 
		$output.="&nbsp;</td>"; 
		$col++; 
	}  
	    
    if($col==8) { 
	    $output.="</tr>"; 
	    $col=1; 
    }
	
}

$output.="</table>";
  
echo $output;

}
else{
	echo 'Unexpected input';
}

?>
