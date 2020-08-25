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
			$redirectUri 	= "http://allamericansports.nl/hub/index.php";
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
				$access_token = $tokenResult['access_token'];
				$refresh_token = $tokenResult['refresh_token'];
		}
			
		}catch(ErrorException $e){
			
			var_dump($e);
			
		}
		
		return array($access_token, $refresh_token);

	}

}
