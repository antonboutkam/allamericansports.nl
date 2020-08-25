<?php
class Settings_paymethod_edit{
    function  run($params){    
        if($params['_do']){
            if($params['paymethod']['price_type']=='free')
                $params['paymethod']['price_amount'] = '';
            
            $id = Paymethod::store($params['paymethod'],$params['id']);
            if(is_numeric($id)&&$id!=0)
                $params['id'] = $id;                 
        }
        if(isset($params['id']) && $params['id']!='new'){
            $paymethod     =   Paymethod::getById( $params['id']);
            if(is_array($paymethod))
                $params    =   array_merge($params,$paymethod);   
        }
  
        if(!$params['price_amount']){
            $params['price_amount'] = '0.00';
        }          
        return $params;
    }
}