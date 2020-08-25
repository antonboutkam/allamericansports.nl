<?php
class ExactConfig{

    private $client_id;
    private $client_secret;
    private $country_code;
    private $division;
    private $return_url;

    public function __construct($sClientId, $sClientSecret, $sCountryCode, $iDivision, $sReturnUrl){

        $sReturnUrl = 'https://backoffice.allamericansports.nl'.$sReturnUrl;
        if(isset($_SERVER['IS_DEVEL'])){
            $sReturnUrl = str_replace('allamericansports.nl', 'allamericansports.nuidev.nl', $sReturnUrl);
        }
        $this->client_id = $sClientId;
        $this->client_secret = $sClientSecret;
        $this->country_code = $sCountryCode;
        $this->division = $iDivision;
        $this->return_url = $sReturnUrl;
        return null;
    }
    public function getReturnUrl(){
        return $this->return_url;
    }
    /**
     * @return string
     */
    public function getDivision()
    {
        return $this->division;
    }
    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }

}
