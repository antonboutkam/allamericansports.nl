<?php

$auth = htmlspecialchars($_GET["auth"]);

//If the correct parameter has been passed
if($auth == '8lYacJ11Z9SBPbgrgUzI'){

	$update_stock_xml = file_get_contents('update_xml_for_amazon.txt');

	include_once ('MarketplaceWebService/Samples/.config.inc.php'); 

	$serviceUrl = "https://mws.amazonservices.de";

	$config = array (
	  'ServiceURL' => $serviceUrl,
	  'ProxyHost' => null,
	  'ProxyPort' => -1,
	  'MaxErrorRetry' => 3,
	);

	 $service = new MarketplaceWebService_Client(
		 AWS_ACCESS_KEY_ID, 
		 AWS_SECRET_ACCESS_KEY, 
		 $config,
		 APPLICATION_NAME,
		 APPLICATION_VERSION);

	$marketplaceIdArray = array("Id" => array('A1PA6795UKMFR9'));
		  
	$feedHandle = @fopen('php://memory', 'rw+');
	fwrite($feedHandle, $update_stock_xml);
	rewind($feedHandle);

	$request = new MarketplaceWebService_Model_SubmitFeedRequest();
	$request->setMerchant(MERCHANT_ID);
	$request->setMarketplaceIdList($marketplaceIdArray);
	$request->setFeedType('_POST_INVENTORY_AVAILABILITY_DATA_');
	$request->setContentMd5(base64_encode(md5(stream_get_contents($feedHandle), true)));
	rewind($feedHandle);
	$request->setPurgeAndReplace(false);
	$request->setFeedContent($feedHandle);
	//$request->setMWSAuthToken('<MWS Auth Token>'); // Optional

	rewind($feedHandle);

	try {
				  $response = $service->submitFeed($request);
				  
					if ($response->isSetSubmitFeedResult()) { 
						$submitFeedResult = $response->getSubmitFeedResult();
						if ($submitFeedResult->isSetFeedSubmissionInfo()) { 
							$feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();
						} 
					}
					if ($response->isSetResponseMetadata()) { 
						$responseMetadata = $response->getResponseMetadata();
					}


		} catch (MarketplaceWebService_Exception $ex) {
			 echo("Caught Exception: " . $ex->getMessage() . "\n");
			 echo("Response Status Code: " . $ex->getStatusCode() . "\n");
			 echo("Error Code: " . $ex->getErrorCode() . "\n");
			 echo("Error Type: " . $ex->getErrorType() . "\n");
			 echo("Request ID: " . $ex->getRequestId() . "\n");
			 echo("XML: " . $ex->getXML() . "\n");
			 echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");
		 }

	@fclose($feedHandle);
	
}


?>