<?php

class Amazon_stock
{

	public function get_stock_amazon()
		{
		
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
	 
		$request = new MarketplaceWebService_Model_RequestReportRequest($parameters);
    
		$request = new MarketplaceWebService_Model_RequestReportRequest();
		$request->setMarketplaceIdList($marketplaceIdArray);
		$request->setMerchant(MERCHANT_ID);
		$request->setReportType('_GET_FLAT_FILE_OPEN_LISTINGS_DATA_');
		//request->setMWSAuthToken('<MWS Auth Token>'); // Optional
    
		//invokeRequestReport($service, $request);
    
		try {
              $response = $service->requestReport($request);

                if ($response->isSetRequestReportResult()) { 
				
                    $requestReportResult = $response->getRequestReportResult();
                    
                    if ($requestReportResult->isSetReportRequestInfo()) {
                        
                        $reportRequestInfo = $requestReportResult->getReportRequestInfo();
                        
                      }
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
	 
	 
	 
	 $my_report_request_id = $reportRequestInfo->getReportRequestId();
	 
	 //echo '<p />Report request id: ' . $my_report_request_id . '<p />';
	 
	 //Get the report ID
	if($my_report_request_id){
		
		$request = new MarketplaceWebService_Model_GetReportListRequest();
		$request->setMerchant(MERCHANT_ID);
		$request->setAvailableToDate(new DateTime('now', new DateTimeZone('UTC')));
		$request->setAvailableFromDate(new DateTime('-3 months', new DateTimeZone('UTC')));
		$request->setAcknowledged(false);
		
		$my_request_id_list = new MarketplaceWebService_Model_IdList();
		$my_request_id_list->setId($my_report_request_id);
		
		$request->setReportRequestIdList($my_request_id_list);
		
		//inital value of report id variable
		$my_report_id = '11';
		
		$i = 0;
		
		//loop to get the reportid once available from the report request list
		while(strlen($my_report_id) < 3 AND $i < 10){
			
			$i++;
			
			sleep(10);
			
				try {
					  $response = $service->getReportList($request);
					  
						if ($response->isSetGetReportListResult()) { 
							
							$getReportListResult = $response->getGetReportListResult();
							
							$reportInfoList = $getReportListResult->getReportInfoList();
							foreach ($reportInfoList as $reportInfo) {

								$my_report_id = $reportInfo->getReportId();
								
							}
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
		 
		
		}
		 
		 //echo '<p /><p />Report id: ' . $my_report_id . '<p />';
		
	}
	
	if(strlen($my_report_id) < 3){ 
		echo 'Time out error: opvragen report request list kost teveel tijd.';
	}
	else{
	
		//Get the actual report in XML format
		$request = new MarketplaceWebService_Model_GetReportRequest();
		$request->setMerchant(MERCHANT_ID);
		$request->setReport(@fopen('php://memory', 'rw+'));
		$request->setReportId($my_report_id);
		//$request->setMWSAuthToken('<MWS Auth Token>'); // Optional
		 
		try {
				  $response = $service->getReport($request);
				  
					if ($response->isSetGetReportResult()) {
					  
					  $getReportResult = $response->getGetReportResult(); 
					  
					}
					if ($response->isSetResponseMetadata()) { 
					
						$responseMetadata = $response->getResponseMetadata();
					   
					}
					
					$response = stream_get_contents($request->getReport());

		 } catch (MarketplaceWebService_Exception $ex) {
			 echo("Caught Exception: " . $ex->getMessage() . "\n");
			 echo("Response Status Code: " . $ex->getStatusCode() . "\n");
			 echo("Error Code: " . $ex->getErrorCode() . "\n");
			 echo("Error Type: " . $ex->getErrorType() . "\n");
			 echo("Request ID: " . $ex->getRequestId() . "\n");
			 echo("XML: " . $ex->getXML() . "\n");
			 echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");
		 }
	 
	 
		//Transform the xml to a list of ids and stock positions 
	 
		$lines = explode(PHP_EOL, $response);
		$producten_amazon = array();
		foreach ($lines as $line) {
			$producten_amazon[] = str_getcsv($line,"\t");
		}
		
	}
	
		return $producten_amazon;
	
	}
		 
}
	

	