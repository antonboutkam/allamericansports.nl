<?php
class ExactAuthenticate{
    private $oExactConfig;


    public function __construct(ExactConfig $oExactConfig){
        $this->oExactConfig = $oExactConfig;
    }
    private function getConfig(){
        if (!$this->oExactConfig instanceof ExactConfig) {
            throw new RuntimeException("Exact config was not set while it should have been.");
        }
        return $this->oExactConfig;
    }

    /**
     * @return string url so send the auth code to.
     * @throws ErrorException
     */
    public function getAuthenticationUrl(){
        if(!$this->oExactConfig instanceof ExactConfig){
            throw new RuntimeException('ExactConfig was not set, try setting it with setConfig first.');
        }


        // $this->oExactConfig->getCountryCode(), $this->oExactConfig->getClientId(), $this->oExactConfig->getClientSecret()

        $oExactApi = new ExactApi($this->oExactConfig, $this->oExactConfig->getDivision());
        $oExactApi->getOAuthClient()->setRedirectUri($this->getConfig()->getReturnUrl());

        $sAuthenticationUrl = $oExactApi->getOAuthClient()->getAuthenticationUrl();
        return $sAuthenticationUrl;
    }



}