<?php
class Psp{
    private $psp;
    
    function __construct(){        
        $psp        = Cfg::get('PSP');        
        $this->psp  = new $psp;
    }
    function getBanks(){
        return $this->psp->getBanks();
    }
    function checkPayment($trxId,$shopId=''){
        return $this->psp->checkPayment($trxId,$shopId);
    }
    function getRedirectUrl($payorder,$relation_id){
        return $this->psp->getRedirectUrl($payorder,$relation_id);
    }
    
}
