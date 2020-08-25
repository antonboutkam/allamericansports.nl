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

require_once 'exactoauth.class.inc.php';

class ExactApi
{
	
	const METHOD_POST = 'post';
	
	const URL_API = 'https://start.exactonline.%s';
	
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
	public function __construct(ExactConfig $oExactConfig, $refreshToken = NULL)
	{
		$this->countryCode = $oExactConfig->getCountryCode();
		$this->clientId = $oExactConfig->getClientId();
		$this->clientSecret = $oExactConfig->getClientSecret();
		$this->refreshToken = $refreshToken;
		$this->division = $oExactConfig->getDivision();
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
     * @param int $expiresInTime
     */
    public function setExpiresIn($expiresInTime, $bCalculate = true)
    {
        if($bCalculate){
            $this->expiresIn = time() + $expiresInTime;
        }else{
            $this->expiresIn = $expiresInTime;
        }
    }
    public function setAccessToken($accessToken){
        $this->accessToken = $accessToken;
    }
    public function getAccessToken(){
        return $this->accessToken;
    }

    public function getExpiresIn(){
        return $this->expiresIn;
    }
    public function getRefreshToken(){
        return $this->refreshToken;
    }

	/**
	 * @return string|FALSE
	 * @throws \ErrorException
	 */
	protected function initAccessToken()
	{

        Log::message('exact_token', "Is expired is ".$this->isExpired(), __METHOD__);

		if (empty($this->accessToken) || $this->isExpired()) {

            Log::message('exact_token', "Exact access token is expired, refreshing now", __METHOD__);

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
		}else{
            Log::message('exact_token', "Exact access token is NOT expired, keep on using current token", __METHOD__);
        }

		return $this->accessToken;
	}


	
	/**
	 * @return int
	 */
	protected function isExpired()
	{
        $sMessage = "Access token expires in ".($this->expiresIn > time()).'';
        Log::message('exact_token', $sMessage, __METHOD__);
		return ($this->expiresIn > time()) ? false : true;
	}
	
	/**
	 * @param string $resourceUrl
	 * @param array|NULL $params
	 * @return string
	 */

	protected function getRequestUrlXMLApi($resourceUrl, $params = NULL)
	{

        $baseUrl = sprintf(self::URL_API, $this->countryCode);
        $apiUrl = $baseUrl.$resourceUrl;
        return $apiUrl.'&access_token='.$params['access_token'];
	}
    protected function getRequestUrl($resourceUrl, $params = NULL)
    {
        $resourceUrlParts = parse_url($resourceUrl);
        $baseUrl = sprintf(self::URL_API, $this->countryCode);
        $apiUrl = $baseUrl . $this->division.'/'.$resourceUrlParts['path'];

        if (isset($resourceUrlParts['query'])) {
            $apiUrl .= '?' . $resourceUrlParts['query'];
        } else
            if ($params && is_array($params)) {
                $apiUrl .= '?' . http_build_query($params, '', '&');
            }

        return $apiUrl;
    }

	/**
     * @param $sDirection (upload|download)
	 * @param string $url
	 * @param string $method
	 * @param mixed $payload
	 * @return string
	 */
	public function sendRequest($sDirection, $url, $method, $payload = NULL)
	{
	    $this->initAccessToken();
        Log::message('exact_token', 'Send request', __METHOD__);
        $sApi = 'getRequestUrl';

        if($sDirection == 'upload'){
            $sApi = 'getRequestUrlXMLApi';
            $url = '/docs/XMLUpload.aspx?'.$url;
        }else if($sDirection == 'download'){
            $sApi = 'getRequestUrlXMLApi';
            $url = '/docs/XMLDownload.aspx?'.$url;
        }else{
            throw new Exception('Direction should be upload or download.');
        }

		$requestUrl = $this->$sApi($url, array(
		    'access_token' => $this->getAccessToken()
		));

        Log::message('exact_token', 'Request url is: '.$requestUrl, __METHOD__);


		// Base cURL option
		$curlOpt = array();
		$curlOpt[CURLOPT_URL] = $requestUrl;
		$curlOpt[CURLOPT_RETURNTRANSFER] = TRUE;
		$curlOpt[CURLOPT_SSL_VERIFYPEER] = TRUE;
		$curlOpt[CURLOPT_HEADER] = false;
			
		if ($method == self::METHOD_POST) {

		    $accessToken = $this->getAccessToken();

			$curlOpt[CURLOPT_HTTPHEADER] = array(
			    'access_token:' . $accessToken
			);
            if(is_array($payload)){
                $curlOpt[CURLOPT_HTTPHEADER][] = 'Content-Type:application/json';
                $curlOpt[CURLOPT_HTTPHEADER][] = 'Content-length: ' . strlen(json_encode($payload));
			    $curlOpt[CURLOPT_POSTFIELDS] = json_encode($payload);
            }else{
                $curlOpt[CURLOPT_POSTFIELDS] = utf8_encode($payload);
            }
			$curlOpt[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
		}
		
		$curlHandle = curl_init();
		curl_setopt_array($curlHandle, $curlOpt);
		
		return curl_exec($curlHandle);
	}

}
