<?php
class Settings_tableeditor{
    function run($params){                   
        $allowedGroups = array('product_type');
                                
        if(!isset($params['table'])||!in_array($params['table'],$allowedGroups)){
            trigger_error('Please specify a group / allowed group',E_USER_ERROR);
            exit();
        }    
        
        if($params['_do'] == 'add')            
            Lookup::add($params['group'],$params['value']);                 	        
        
        if($params['_do'] == 'delete')           
            Lookup::delete($params['group'],$params['id']);
               	        
        if($params['_do'] == 'update')           
            Lookup::update($params['group'],$params['newval'],$params['id']);                          	
                
        $itemsPP                    = 39;
        $params['current_page']     = $params['current_page']?(int)$params['current_page']:1;                
        $params['items']            = Lookup::getItems($params['group'],$params['current_page'],$itemsPP);
        
        $oneColTables = array('delivery_time','product_brand','product_width_height');
        $cols = in_array($params['table'],$oneColTables)?1:3;
        
        $params['items']['data']    = array_columns($params['items']['data'],$cols);
        #pre_r( $params['items']['data'] );
        return $params;
    }
}