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

require_once 'ExactApi_via_xml.php';

class Exact_stock
{

	public function get_stock_exact($system)
		{
			
		if($system == 'Amazon'){
			// Configuration, change these:
			$clientId 		= '{8dff146a-9377-455c-adbc-d826c7e4de9f}';
			$clientSecret 	= 'YkB5tZc2DHm8';
			$redirectUri 	= "https://allamericansports.nl/update/Voorraad_synchroniseren_amazon.php";
		}elseif($system == 'Bol'){
			// Configuration, change these:
			$clientId 		= '{be737814-828b-40d3-994b-e17610076024}';
			$clientSecret 	= 'BH9apfpnES1z';
			$redirectUri 	= "http://allamericansports.nl/update/Voorraad_synchroniseren.php";
		}
		
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
				$next_guid= '101010xx';
				
				//safe to limit the number of request when something goes wrong
				$i = 0;

				while(strlen($next_guid) > 1 and $i < 100){
					
					$i = $i + 1;
					//echo $i . '<br />';
			
					$parameters = array(
						'Topic' => 'StockPositions',
						'_Division_' => '325848',
						'Warehouse' => '1'
						);
					
					if($i > 1){
						$parameters['TSPaging'] = substr($next_guid, -18);
					}

					// Get stock details
					$response = $exactApi->sendRequest('docs/XMLDownload.aspx', 'get', $parameters);
						
					$xml=new SimpleXMLElement($response);
					
					$new_counts = array();
					
					$items = $xml->StockPositions->StockPosition;
					
					for ($x = 0; $x < count($items); $x++) {
						
						if((string)$items[$x]->Warehouse['code'] == '1'){
							
							$new_counts[$x][0] = (string)$items[$x]->Item['code'];
							$new_counts[$x][1] = (string)$items[$x]->CurrentQuantity;
							$new_counts[$x][2] = '0';   //(string)$items[$x]->Planning-In;
							$new_counts[$x][3] = '0' ;  //(string)$items[$x]->Planning-Out;
							$new_counts[$x][4] = (string)$items[$x]->Item->Description;
						
						//echo $new_counts[$x][0] . ' - ' . $new_counts[$x][1] . ' - ' . $new_counts[$x][2] . ' - ' . $new_counts[$x][3] . ' - ' . $new_counts[$x][4] . ' - ' . (string)$items[$x]->Warehouse['code'] . '<br />';
						}
					}
					
					//echo '<p />';
					
					$Exact_counts = array_merge($Exact_counts, $new_counts);
					
					$link = $xml->Topics->Topic;
					
					if(count($link) == 1){
						$next = $link;
						//echo $next->attributes()->ts_d;
						$next_guid = $next->attributes()->ts_d;
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