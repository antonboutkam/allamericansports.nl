<?php
class Products_spotlight{
    function  run($params){        
        if($params['_do']=='add_product')
            ProductDao::setVal('in_spotlight',1,$params['id']);
        
        if($params['_do']=='remove_product')
            ProductDao::setVal('in_spotlight',0,$params['id']);
                        
        $filter['c.in_spotlight']   = 1;        
        $params['products']         = ProductDao::find($filter, $sort,null,1,100, true);           
        $params['content']          = parse('products_spotlight',$params);
        return $params;
    }    
}