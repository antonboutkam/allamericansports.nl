<?php
class Stock_current{
    /**
     * Stock::run()
     * 
     * @param mixed $params
     * @return
     */
    function  run($params){

        if($params['_do']=='move'){
            $params['id']           =   DeliveryDao::createBlank('internal');
            // Decrement product location
            DeliveryDao::insertDeliveryRecord($params['id'],$params['productId'],$params['locationId'],(0 - $params['quantity']));
            // Increment new product location
            DeliveryDao::insertDeliveryRecord($params['id'],$params['productId'],$params['newLocationId'],$params['quantity']); 
            DeliveryDao::complete($params['id']);           
        }
        if($params['_do']=='remove'){
            $params['id']           =   DeliveryDao::createBlank($params['reason']);
            $configId               = StockDao::getConfigIdByStockId($params['locationId']);
            DeliveryDao::insertDeliveryRecord($params['id'],$params['productId'],$configId,$params['quantity']);
            DeliveryDao::complete($params['id']);
        }
        $params['current_page']     = ($params['current_page'])?$params['current_page']:1;          
        $params['location']         = (isset($params['location']))?$params['location']:User::getLocaton();
        $params['location_name']    = Location::getName($params['location']);
        $params['locations']        = WarehouseDao::getLocations();                           
        $params['sort']             = ($params['sort'])?$params['sort']:'article_number';

        $filter['wl.id']            = $params['location'];
        if(trim($params['query'])!='' && $params['query']!=$params['defaultquery'])
            $manualWhere            = sprintf('AND (article_number LIKE "%%%1$s%%" OR
                                                article_name LIKE "%%%1$s%%" OR
                                                description LIKE "%%%1$s%%")',$params['query']);

        if($params['sort']=='position')
            $sort = 'path ASC, rack ASC, shelf ASC';
        else if($params['sort']=='position DESC')
            $sort = 'path DESC, rack DESC, shelf DESC';
        else{
            $sort = $params['sort'];
        }
        $filter['c.deleted']        = array('operator'=>'IS ','value'=>'NULL');        
        $products                   = StockDao::find($sort,$params['current_page'],$filter,$manualWhere);
                              
        $params['products']         = $products['data'];
        
        unset($products['data']);
        $params                     = array_merge($params,$products);
        $params['paginate']         = paginate($params['current_page'],$params['rowcount']);            
        $params['stock_tbl']        = parse('inc/stock_tbl',$params);
        
        if($params['ajaxresult'])
            exit(json_encode($params));                                
        return $params;
    }
}