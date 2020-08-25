<?php
class ExactService{

    protected $iDivision;
    protected $oExactApi;

    function __construct(ExactApi $oExactApi, $iDivision){
        if(!is_numeric($iDivision)){
            throw new InvalidArgumentException("Exact service object expects an integer value for the divison in its constructor");
        }
        $this->iDivision = $iDivision;
        $this->oExactApi = $oExactApi;
    }
    function getDivision(){
        return $this->iDivision;
    }

    function getApi(){
        if(!$this->oExactApi instanceof ExactApi){
            throw new RuntimeException("Exact API should be an instance of Exact API");
        }
        return $this->oExactApi;

    }
    static function xml2Array($xml){
        return json_decode(json_encode((array) simplexml_load_string($xml)), 1);
    }

}