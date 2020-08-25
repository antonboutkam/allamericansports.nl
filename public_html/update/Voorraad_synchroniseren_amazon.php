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
	$Exact_stock_counts = $Exact_stock->get_stock_exact('Amazon');
}

require_once 'Amazon_stock.php';
$Amazon_stock = new Amazon_stock();

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
        <h2>Voorraad Amazon synchroniseren</h2>
	</div>
	
    <div class="alert alert-success" role="alert">
	
		<?php $len_exact = count($Exact_stock_counts); 
		echo 'Exact succesvol geladen (' . $len_exact . ' producten)'; ?>
		
    </div>
	
	<div class="alert alert-success" role="alert">
	
		<?php $Amazon_stock_counts = $Amazon_stock->get_stock_amazon();
		$len_amazon = count($Amazon_stock_counts);
		echo 'Amazon succesvol geladen (' . $len_amazon . ' producten)'; ?>
		
    </div>

<?php

//$Amazon_stock_counts = $Amazon_stock->get_stock_amazon();

//echo 'Count amazon: ' . count($Amazon_stock_counts) . '<p />';

//for($i = 0; $i<100;$i++){
//	echo $Amazon_stock_counts[$i][0] . ' - ' . $Amazon_stock_counts[$i][1] . ' - ' . $Amazon_stock_counts[$i][2] . ' - ' . $Amazon_stock_counts[$i][3] . '<br />';
//}



$array_stock_updates = array();
$id = 1;

//find matches between the two lists , skip first item which is the header row
for($i = 1 ; $i < $len_amazon ; $i++)
{
	//track whether a match is found
    $found_match_in_exact = 0;
    
	//add leading zeros to the amazon code to come to a total of 13 characters
	if(strlen($Amazon_stock_counts[$i][0]) < 13){ $leading_zeros = str_repeat("0", 13 - strlen($Amazon_stock_counts[$i][0])); }else{$leading_zeros = '';}
	$code_amazon = $leading_zeros . $Amazon_stock_counts[$i][0];
		
   //$code_amazon = $Amazon_stock_counts[$i][0];
	
   for($j = 0 ; $j < $len_exact ; $j++)
   {
		//add leading zeros to the exact code to come to a total of 13 characters
		if(strlen($Exact_stock_counts[$j][0]) < 13){ $leading_zeros = str_repeat("0", 13 - strlen($Exact_stock_counts[$j][0])); }else{$leading_zeros = '';}
		$code_exact = $leading_zeros . $Exact_stock_counts[$j][0];
		$stock_exact = max(intval($Exact_stock_counts[$j][1]) - intval($Exact_stock_counts[$j][3]),0);
		
	   //if the codes are equal, a match is found
	   if(strcmp($code_amazon,$code_exact) == 0)
	   {
			$found_match_in_exact++;
			
			//zet voorraad op 0 als er meerdere hits zijn
			if($found_match_in_exact > 1){
				
				$array_stock_updates[$code_amazon][0] =  "<Message>\r\n<MessageID>" . $id . "</MessageID>\r\n<OperationType>Update</OperationType>\r\n<Inventory>\r\n <SKU>" . $Amazon_stock_counts[$i][0] . "</SKU>\r\n <Quantity>0</Quantity>\r\n</Inventory>\r\n</Message>\r\n";
				$array_stock_updates[$code_amazon][2] =  $code_amazon . ' - ' . $Amazon_stock_counts[$i][1] . ' - ' . $Amazon_stock_counts[$i][2];
				$array_stock_updates[$code_amazon][1] =  '';
				$id++;
			}
			//if there is an update to be made
			elseif($Amazon_stock_counts[$i][3] != $stock_exact){
						
				//zet voorraad op waarde uit Exact
				$array_stock_updates[$code_amazon][0] =  "<Message>\r\n<MessageID>" . $id . "</MessageID>\r\n<OperationType>Update</OperationType>\r\n<Inventory>\r\n <SKU>" . $Amazon_stock_counts[$i][0] . "</SKU>\r\n <Quantity>" . $stock_exact . "</Quantity>\r\n</Inventory>\r\n</Message>\r\n";
				$array_stock_updates[$code_amazon][1] =  $code_amazon . ' - ' . $Exact_stock_counts[$j][4] . '<br />Van ' . $Amazon_stock_counts[$i][3] . ' naar ' . (int)$stock_exact . ' stuks';
				$id++;
			} 
	   }
   }

   if($found_match_in_exact == 0){
	   //No match in Exact has been found
	   $array_stock_updates[$code_amazon][3] = $code_amazon . ' - ' . $Amazon_stock_counts[$i][3];
   }
}




//Initialize update xml request
$update_stock_xml = <<<EOD
<?xml version="1.0" encoding="utf-8" ?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
<Header>
<DocumentVersion>1.01</DocumentVersion>
<MerchantIdentifier>A2ZMTSE1ZLDP3P</MerchantIdentifier>
</Header>
<MessageType>Inventory</MessageType>\r\n
EOD;


$count_updates = 0;
foreach($array_stock_updates as $item) {
	$update_stock_xml = $update_stock_xml . $item[0];
	if(strlen($item[0]) > 1){$count_updates++;}
}

$update_stock_xml = $update_stock_xml . "</AmazonEnvelope>";

?>

<div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title"><strong>Deze updates worden gemaakt in Amazon</strong></h3>
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
              <h3 class="panel-title"><strong>Producten die meerdere keren in Exact gevonden zijn</strong><br />(Voorraad in Amazon wordt op 0 gezet)</h3>
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

$_SESSION['xml_variable_amazon'] = $update_stock_xml;

$_SESSION['stock_exact_variable'] = $Exact_stock_counts;

//Update stock of Amazon
if($count_updates > 0){
?>
	<p />	
	<form method="post" action="Submit_update_amazon.php">
		<button type="submit" class="btn btn-primary">Ok, verzend update naar Amazon</button>
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

