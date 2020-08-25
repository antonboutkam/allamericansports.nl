<?php

/**
* Exact API / oAauth
* Copyright (c) iWebDevelopment B.V. (https://www.iwebdevelopment.nl)
*
* Licensed under The MIT License
* For full copyright and license information, please see the LICENSE.txt
* Redistributions of files must retain the above copyright notice.
*
* @copyright     Copyright (c) iWebDevelopment B.V. (https://www.iwebdevelopment.nl)
* @link          https://www.iwebdevelopment.nl
* @since         01-06-2015
* @license       http://www.opensource.org/licenses/mit-license.php MIT License
*/

require_once 'ExactApi.php';

class Exact_stock
{

	public function get_stock_exact()
		{
			
		// Configuration, change these:
		$clientId 		= '{be737814-828b-40d3-994b-e17610076024}';
		$clientSecret 	= 'BH9apfpnES1z';
		$redirectUri 	= "http://allamericansports.nl/update/Voorraad_synchroniseren.php";
		$division		= "325848";

		try {
			
			// Initialize ExactAPI
			$exactApi = new ExactApi('nl', $clientId, $clientSecret, $division);
			
			$exactApi->getOAuthClient()->setRedirectUri($redirectUri);
			
			if (!isset($_GET['code'])) {
				
				// Redirect to Auth-endpoint
				$authUrl = $exactApi->getOAuthClient()->getAuthenticationUrl();
				header('Location: ' . $authUrl, TRUE, 302);
				die('Redirect');
				
			} else {
				
				$time_api = microtime(true);
				$time_xml = microtime(true);

				// Receive data from Token-endpoint
				$tokenResult = $exactApi->getOAuthClient()->getAccessToken($_GET['code']);
				$exactApi->setRefreshToken($tokenResult['refresh_token']);
					
				$Exact_counts = array();
				$next_guid= 'to_be_filled_with_next_guid';
				
				//safe to limit the number of request when something goes wrong
				$i = 0;

				while(strlen($next_guid) > 1 and $i < 300){

					$i = $i + 1;
			
					$parameters = array(
						'$select' => 'ID,Code,Stock,Description',
						//'$filter' => "Stock ge 3"
						//'$filter' => $codes_bol
						'$filter' => "startswith(Code, '0') eq true or startswith(Code, '1') eq true or startswith(Code, '2') eq true or startswith(Code, '3') eq true or startswith(Code, '4') eq true or startswith(Code, '5') eq true or startswith(Code, '6') eq true or startswith(Code, '7') eq true or startswith(Code, '8') eq true or startswith(Code, '9') eq true"
						);
					
					if($i > 1){
						$parameters['$skiptoken'] = $next_guid;
					}

					// Get stock details
					$response = $exactApi->sendRequest('logistics/Items', 'get', $parameters);
							
					$xml=new SimpleXMLElement($response);
					
					$xml->registerXPathNamespace('d', 'http://schemas.microsoft.com/ado/2007/08/dataservices');
					$xml->registerXPathNamespace('m', 'http://schemas.microsoft.com/ado/2007/08/dataservices/metadata');
					
					$result = $xml->xpath('//d:Code | //d:Stock');
					
					$new_counts = array();
					$j = 0;
					
					for ($x = 0; $x < count($result)-1; $x=$x+2) {
						$new_counts[$j][0] = $result[$x][0];
						if(strlen($result[$x+1][0]) > 0){ $new_counts[$j][1] = $result[$x+1][0]; }else{ $new_counts[$j][1] = '0'; };
						$j++;
					}
					
					$Exact_counts = array_merge($Exact_counts, $new_counts);
					
					$link = $xml->link;
					
					if(count($link) == 2){
						$next = $link[1];
						$next_url = $next->attributes()->href;
						
						$parts = parse_url($next_url);
						parse_str($parts['query'], $query);
						$next_guid = $query['$skiptoken'];
					}else{
						$next_guid = '';
					}

				}
			
		}
			
		}catch(ErrorException $e){
			
			var_dump($e);
			
		}

		return $Exact_counts;
	}

}

