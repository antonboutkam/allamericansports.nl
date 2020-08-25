<?php
class Stock_serialnumbers{
    function  run($params){
        if($params['_do']=='delete')
            ProductSerial::delete($params['data']);

        if($params['_do']=='filter')
            $query = $params['data'];
        
        if($params['_do'] == 'add')
            ProductSerial::register($params['data']);
        
        $params['serials'] = ProductSerial::find($query,1,20);
        $params['serial_table'] = parse('inc/product_serials',$params);
        return $params;
    }
}