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

require_once 'exactapi.class.inc.php';

// Configuration, change these:
$clientId 		= '{00000000-0000-0000-0000-000000000000}';
$clientSecret 	= 'ABCDeFGHijKLm';
$redirectUri 	= "http://www.domain.com";
$division		= "12345";

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
		
		// Receive data from Token-endpoint
		$tokenResult = $exactApi->getOAuthClient()->getAccessToken($_GET['code']);
		$exactApi->setRefreshToken($tokenResult['refresh_token']);
		
		// List accounts
		$response = $exactApi->sendRequest('current/Me', 'get');
		var_dump($response);
		
		// Create account
		$response = $exactApi->sendRequest('crm/Accounts', 'post', array(
			'Status'			=>	'C',
			'IsSupplier'		=>	True,
			'Name'				=>	'iWebDevelopment B.V.',
			'AddressLine1'		=>	'Ceresstraat 1',
			'Postcode'			=>	'4811CA',
			'City'				=>	'Breda',
			'Country'			=>	'NL',
			'Email'				=>	'info@iwebdevelopment.nl',
			'Phone'				=>	'+31(0)76-7002008',
			'Website'			=>	'www.iwebdevelopment.nl'

		));
		var_dump($response);
		
	}
	
}catch(ErrorException $e){
	
	var_dump($e);
	
}