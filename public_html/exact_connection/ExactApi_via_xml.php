<?php

/**
* Exact API
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

require_once 'ExactOAuth.php';

class ExactApi
{
	
	const METHOD_POST = 'post';
	
	const URL_API = 'https://start.exactonline.%s/';
	
	/** @var string */
	protected $countryCode;

	/** @var string */
	protected $clientId;

	/** @var string */
	protected $clientSecret;

	/** @var string */
	protected $refreshToken;
	
	/** @var string */
	protected $accessToken;
	
	/** @var int */
	protected $expiresIn;
	
	/** @var string */
	protected $division;

	/** @var ExactOAuth */
	protected $oAuthClient;
	

	/**
	 * @param string $countryCode
	 * @param string $clientId
	 * @param string $clientSecret
	 * @param string $division
	 * @param string|NULL $refreshToken
	 */
	public function __construct($countryCode, $clientId, $clientSecret, $division, $refreshToken = NULL)
	{
		$this->countryCode = $countryCode;
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		$this->refreshToken = $refreshToken;
		$this->division = $division;
	}
	
	/**
	 * @return ExactOAuth
	 */
	public function getOAuthClient()
	{
		if (!$this->oAuthClient) {
			$this->oAuthClient = new ExactOAuth(
				$this->countryCode, $this->clientId, $this->clientSecret
			);
		}
		
		return $this->oAuthClient;
	}
	
	/**
	 * @param string $token
	 */
	public function setRefreshToken($token)
	{
		$this->refreshToken = $token;
	}

	/**
	 * @return string|FALSE
	 * @throws \ErrorException
	 */
	protected function initAccessToken()
	{
		if (empty($this->accessToken) || $this->isExpired()) {
			
			if (empty($this->refreshToken)) {
				throw new \ErrorException('Refresh token is not specified.');
			}
			
			$refreshed =  $this->getOAuthClient()->refreshAccessToken($this->refreshToken);
			if (!$refreshed) {
				return FALSE;
			}
			$this->setExpiresIn($refreshed['expires_in']);
			$this->refreshToken = $refreshed['refresh_token'];
			$this->accessToken = $refreshed['access_token'];
		}
		
		return $this->accessToken;
	}

	/**
	 * @param int $expiresInTime
	 */
	protected function setExpiresIn($expiresInTime)
	{
		$this->expiresIn = time() + $expiresInTime;
	}
	
	/**
	 * @return int
	 */
	protected function isExpired()
	{
		return $this->expiresIn > time();
	}
	
	/**
	 * @param string $resourceUrl
	 * @param array|NULL $params
	 * @return string
	 */
	protected function getRequestUrl($resourceUrl, $params = NULL)
	{
		$resourceUrlParts = parse_url($resourceUrl);
		$baseUrl = sprintf(self::URL_API, $this->countryCode);
		$apiUrl = $baseUrl . $resourceUrlParts['path'];
		
		if (isset($resourceUrlParts['query'])) {
			$apiUrl .= '?' . $resourceUrlParts['query'];
		} else
		if ($params && is_array($params)) {
			$apiUrl .= '?' . http_build_query($params, '', '&');
		}
		
		return $apiUrl;
	}
	
	/**
	 * @param string $url
	 * @param string $method
	 * @param array|NULL $payload
	 * @return string
	 */
	public function sendRequest($url, $method, $parameters, $payload = NULL)
	{
		if ($payload && !is_array($payload)) {
			throw new \ErrorException('Payload is not valid.');
		}
		
		if (!$accessToken = $this->initAccessToken()) {
			throw new \ErrorException('Access token was not initialized');
		}
		
		$parameters['access_token'] = $accessToken;
		
		$requestUrl = $this->getRequestUrl($url, $parameters);
		
		//echo $requestUrl . '<p />';
		
		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => $requestUrl,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"postman-token: 6face98f-1309-8339-73e0-db03423688ad"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		
		return $response;
	}

}
