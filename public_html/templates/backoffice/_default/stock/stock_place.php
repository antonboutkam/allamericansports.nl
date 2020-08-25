<?php
class Stock_place{
    function  run($params){
/*
        $params['rand']                 =   rand(0,999999);
        if($params['articleNumber'])
            $result['productId']        =   ProductDao::getIdBy('article_number',$params['articleNumber']);            
        // A user can only do one delivery at a time
        $params['id']                   =   DeliveryDao::getUserUnfinishedDeliveryId(User::getId());        
        if($params['_do']=='update_location')
            DeliveryDao::updateLocation($params['locationId'],$params['stockId']);
        if($params['_do']=='delete')
            DeliveryDao::remove($params['stockId']);
        if($params['_do']=='clear'){
            DeliveryDao::removeDelivery($params['id']);
            unset($params['id']);
        }
        if($params['_do']=='update_quantity'){
            DeliveryDao::updateQuantity($params['val'],$params['stockId']);
            exit();
        }
        if($params['_do']=='complete'){
            $params['incomplete'] = DeliveryDao::completable($params['id']);
            if(!$params['incomplete'])
                DeliveryDao::complete($params['id']);
            exit(json_encode($params));
        }                                                
        $params['location']             =   WarehouseDao::getLocation(User::getLocaton());        
        $params['curr_location']        =   User::getLocaton();
        $params['curr_location_name']   =   User::getLocationName();
                
        if($params['productId']){
            if(!$params['id'])
                $params['id']           =   DeliveryDao::createBlank();            
            DeliveryDao::addProductToStock($params['id'],$params['productId']);                
        }
 $params['showwostock']          =   true;
 */
        
        $params['delivery']             =   PlaceDao::getDelivery($params['did']);
 
        $params['product_find_form']    =   parse('inc/product_find_form',$params);                                                   
        if($params['did'])
            $params['stock_fill_tbl']   =   parse('inc/stock_fill_tbl',$params);                   
        if($params['ajax'])
            exit(json_encode($params));
        return $params;
    }    
}