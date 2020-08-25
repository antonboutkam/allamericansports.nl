<?php
class Settings_paymethods{
    function  run($params){
        
        if($params['_do']=='delete_paymethod')
            Paymethod::delete($params['id']);
        
        $paymethods                 =   Paymethod::getAll('name');
        $params['paymethods']       =   $paymethods['data'];
        $params['rowcount']         =   $paymethods['rowcount'];         
        $params['paymethods_tbl']   =   parse('inc/paymethods_tbl',$params);  
        return $params;
    }
}