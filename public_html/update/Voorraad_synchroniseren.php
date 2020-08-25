<?php
session_start();

$valid_passwords = array ("austin" => "update");
$valid_users = array_keys($valid_passwords);

$user = $_SERVER['PHP_AUTH_USER'];
$pass = $_SERVER['PHP_AUTH_PW'];

$validated = (in_array($user, $valid_users)) && ($pass == $valid_passwords[$user]);

if (!$validated) {
  header('WWW-Authenticate: Basic realm="My Realm"');
  header('HTTP/1.0 401 Unauthorized');
  die ("Not authorized");
}

if (isset($_SESSION['stock_exact_variable'])){
	$Exact_stock_counts = $_SESSION['stock_exact_variable'];
}else{
	require_once 'Exact_stock_via_xml.php';
	$Exact_stock = new Exact_stock();
	$Exact_stock_counts = $Exact_stock->get_stock_exact('Bol');
}

require_once 'Bol_stock.php';
$Bol_stock = new Bol_stock();

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Voorraad update</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/theme.css" rel="stylesheet">

    <script src="js/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

      <!-- Fixed navbar -->
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">All-American Sports</a>
        </div>
        <!--<div id="navbar" class="navbar-collapse collapse">
         <ul class="nav navbar-nav">
           <li class="active"><a href="#">Home</a></li>
           <li><a href="#about">About</a></li>
           <li><a href="#contact">Contact</a></li>
         </ul>
       </div><!--/.nav-collapse -->
      </div>
    </nav>
	
	<div class="container theme-showcase" role="main">
	
	<div class="page-header">
        <h2>Voorraad bol.com synchroniseren</h2>
	</div>
	
	<?php
	
		date_default_timezone_set('GMT');
	
		#$file = escapeshellarg('log/update_bol_log.txt'); // for the security concious (should be everyone!)
		$file = 'log/update_bol_log.txt'; 
		$line = `tail -n 1 $file`;
		
		$date1 = date_create(date('d-m-Y H:i:s'));
		$date2 = date_create($line);
		
		if(date_diff($date1,$date2)->d == 0 AND date_diff($date1,$date2)->m == 0 AND date_diff($date1,$date2)->y == 0 AND date_diff($date1,$date2)->h == 0){
	?>
		<div class="alert alert-info" role="alert">
			Let op! Voorraad bol.com is recent opgevraagd. Updates zijn misschien nog niet zichtbaar.<br />
			Over <?php echo (60 - date_diff($date1,$date2)->i); ?> minuten is de versie up to date
		</div>
	<?php
		}else{	
			$date_time_for_log = date('d-m-Y H:i:s');
			$myfile = file_put_contents('log/update_bol_log.txt', $date_time_for_log.PHP_EOL , FILE_APPEND | LOCK_EX);
		}
	?>
	
    <div class="alert alert-success" role="alert">
	
		<?php $len_exact = count($Exact_stock_counts); 
		echo 'Exact succesvol geladen (' . $len_exact . ' producten)'; ?>
		
    </div>

	<div class="alert alert-success" role="alert">
	
		<?php $Bol_stock_counts_orig = $Bol_stock->get_stock_bol();
		
		/*for($q = 0; $q < count($Bol_stock_counts_orig); $q++)
		{
			echo $Bol_stock_counts_orig[$q][0] . ' - ' . $Bol_stock_counts_orig[$q][1] . ' - ' . $Bol_stock_counts_orig[$q][2] . ' - ' . $Bol_stock_counts_orig[$q][3] . ' - ' . $Bol_stock_counts_orig[$q][4] . ' - ' . $Bol_stock_counts_orig[$q][5] . ' - ' . $Bol_stock_counts_orig[$q][6] . ' - ' . $Bol_stock_counts_orig[$q][7] . ' - ' . $Bol_stock_counts_orig[$q][8] . ' - ' . $Bol_stock_counts_orig[$q][9];
			echo '<br />';
		}*/
		
		$len_bol_total = count($Bol_stock_counts_orig);
		
		$Bol_stock_counts = array_values(array_filter($Bol_stock_counts_orig, function ($var) {
		return ($var['9'] == 'FBR');
		}));

		$len_bol = count($Bol_stock_counts);
		$diff_len = $len_bol_total - $len_bol;
		echo 'Bol.com succesvol geladen (' . $len_bol . ' producten uit eigen voorraad, en ' . $diff_len . ' lvb producten)';
		?>
		
    </div>
	 
<?php

$array_stock_updates = array();

//find matches between the two lists , skip first item which is the header row
for($i = 1 ; $i < $len_bol ; $i++)
{
	//track whether a match is found
   $found_match_in_exact = 0;
   $code_bol = $Bol_stock_counts[$i][0];
	
   for($j = 0 ; $j < $len_exact ; $j++)
   {
		//add leading zeros to the exact code to come to a total of 13 characters
		if(strlen($Exact_stock_counts[$j][0]) < 13){ $leading_zeros = str_repeat("0", 13 - strlen($Exact_stock_counts[$j][0])); }else{$leading_zeros = '';}
		$code_exact = $leading_zeros . $Exact_stock_counts[$j][0];
		
		//Voorraad die we als waar aanhouden is CurrentQuantity - Planning-Out.
		$stock_exact = max(intval($Exact_stock_counts[$j][1]) - intval($Exact_stock_counts[$j][3]),0);
		
	   //if the codes are equal, a match is found
	   if(strcmp($code_bol,$code_exact) == 0)
	   {
			$found_match_in_exact++;
			
			//zet voorraad op 0 als er meerdere hits zijn
			if($found_match_in_exact > 1){
				$array_stock_updates[$code_bol][0] =  "<RetailerOffer>\r\n <EAN>" . $code_bol . "</EAN>\r\n <Condition>" . $Bol_stock_counts[$i][1] . "</Condition>\r\n <Price>" . $Bol_stock_counts[$i][2] . "</Price>\r\n <DeliveryCode>" . $Bol_stock_counts[$i][3] . "</DeliveryCode>\r\n <QuantityInStock>0</QuantityInStock>\r\n <Publish>" . strtolower($Bol_stock_counts[$i][5]) . "</Publish>\r\n <ReferenceCode>" . $Bol_stock_counts[$i][6] . "</ReferenceCode>\r\n <Description>" . $Bol_stock_counts[$i][7] . "</Description>\r\n <Title>" . $Bol_stock_counts[$i][8] . "</Title>\r\n <FulfillmentMethod>" . $Bol_stock_counts[$i][9] . "</FulfillmentMethod>\r\n </RetailerOffer>\r\n";
				$array_stock_updates[$code_bol][2] =  $code_bol . ' - ' . $Bol_stock_counts[$i][4] . ' - ' . $Bol_stock_counts[$i][8];
				$array_stock_updates[$code_bol][1] =  '';
			}
			//if there is an update to be made
			elseif($Bol_stock_counts[$i][4] != $stock_exact){
						
				//zet voorraad op waarde uit Exact
				$array_stock_updates[$code_bol][0] =  "<RetailerOffer>\r\n <EAN>" . $code_bol . "</EAN>\r\n <Condition>" . $Bol_stock_counts[$i][1] . "</Condition>\r\n <Price>" . $Bol_stock_counts[$i][2] . "</Price>\r\n <DeliveryCode>" . $Bol_stock_counts[$i][3] . "</DeliveryCode>\r\n <QuantityInStock>" . $stock_exact . "</QuantityInStock>\r\n <Publish>" . strtolower($Bol_stock_counts[$i][5]) . "</Publish>\r\n <ReferenceCode>" . $Bol_stock_counts[$i][6] . "</ReferenceCode>\r\n <Description>" . $Bol_stock_counts[$i][7] . "</Description>\r\n <Title>" . $Bol_stock_counts[$i][8] . "</Title>\r\n <FulfillmentMethod>" . $Bol_stock_counts[$i][9] . "</FulfillmentMethod>\r\n </RetailerOffer>\r\n";
				$array_stock_updates[$code_bol][1] =  $code_bol . ' - ' . $Bol_stock_counts[$i][8] . '<br />Van ' . $Bol_stock_counts[$i][4] . ' naar ' . (int)$Exact_stock_counts[$j][1] . ' stuks';
			} 
	   }
   }

   if($found_match_in_exact == 0){
	   //No match in Exact has been found
	   $array_stock_updates[$code_bol][3] = $code_bol . ' - ' . $Bol_stock_counts[$i][4] . ' - ' . $Bol_stock_counts[$i][8];
   }
}

////Initialize update xml request
//$update_stock_xml = "<UpsertRequest xmlns=\"https://plazaapi.bol.com/offers/xsd/api-2.0.xsd\">\r\n";
//
//
//$count_updates = 0;
//foreach($array_stock_updates as $item) {
//	$update_stock_xml = $update_stock_xml . $item[0];
//	if(strlen($item[0]) > 1){$count_updates++;}
//}
//
//$update_stock_xml = $update_stock_xml . "</UpsertRequest>";

//initialize array with updates
$update_stock_xml = array();

$count_updates = 0;
foreach($array_stock_updates as $item) {
	$update_stock_xml[floor($count_updates / 49)] = $update_stock_xml[floor($count_updates / 49)] . $item[0];
	if(strlen($item[0]) > 1){$count_updates++;}
}

//add header and footer to each part
foreach($update_stock_xml as $key => $xml) {
	$update_stock_xml[$key] = "<UpsertRequest xmlns=\"https://plazaapi.bol.com/offers/xsd/api-2.0.xsd\">\r\n" . $xml . "</UpsertRequest>";
}

?>

<div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title"><strong>Deze updates worden gemaakt in bol.com</strong></h3>
            </div>
            <div class="panel-body">
				
				<table class="table table-striped">
					<tbody>
					
					<?php foreach($array_stock_updates as $item) {if(strlen($item[1]) > 1){echo '<tr><td>' . $item[1] . '</td></tr>';}} ?>
					
					</tbody>
				</table>
            </div>
</div>

<div class="panel panel-danger">
            <div class="panel-heading">
              <h3 class="panel-title"><strong>Producten die meerdere keren in Exact gevonden zijn</strong><br />(Voorraad in bol.com wordt op 0 gezet)</h3>
            </div>
            <div class="panel-body">
				
				<table class="table table-striped">
					<tbody>
					
					<?php foreach($array_stock_updates as $item) {if(strlen($item[2]) > 1){echo '<tr><td>' . $item[2] . '</td></tr>';}}?>
					
					</tbody>
				</table>
            </div>
          </div>
		  
<div class="panel panel-danger">
            <div class="panel-heading">
              <h3 class="panel-title"><strong>Producten die in Exact op inactief staan of niet gevonden zijn</strong></h3>
            </div>
            <div class="panel-body">
				
				<table class="table table-striped">
					<tbody>
								
					<?php foreach($array_stock_updates as $item) {if(strlen($item[3]) > 1){echo '<tr><td>' . $item[3] . '</td></tr>';}}?>
						
					</tbody>
				</table>				
            </div>
          </div>

<?php

$_SESSION['xml_variable'] = $update_stock_xml;

$_SESSION['stock_exact_variable'] = $Exact_stock_counts;

//Update stock of Bol.com
if($count_updates > 0){
?>
	<p />	
	<form method="post" action="Submit_update.php">
		<button type="submit" class="btn btn-primary">Ok, verzend update naar bol.com</button>
	</form>
<?php
}else{
	
	echo 'Geen updates.';

}


?>

    </div> <!-- /container -->
	
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/vendor/jquery.min.js"><\/script>')</script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/docs.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>

