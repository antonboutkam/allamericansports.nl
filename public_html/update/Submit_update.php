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

require_once 'Bol_update_stock.php';

$update_stock_xml = $_SESSION['xml_variable'];

$Bol_update_stock = new Bol_update();

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
		
		<div class="alert alert-success" role="alert">
		
		<?php 
		    $count_requests = 0;
			foreach($update_stock_xml as $xml_to_send) {
				$count_requests++;
				echo $Bol_update_stock->update_stock_bol($xml_to_send);
				//echo $xml_to_send;
				echo ' - ' . $count_requests . '<br />';
			}
		?>
			
		</div>
		
		<div class="list-group">
            <a href="Voorraad_synchroniseren_amazon.php" class="list-group-item">Om Amazon ook te updateten, klik hier</a>
        </div>
		
		<div class="list-group">
            <a href="index.php" class="list-group-item">Terug naar homepagina</a>
        </div>
		
	
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
