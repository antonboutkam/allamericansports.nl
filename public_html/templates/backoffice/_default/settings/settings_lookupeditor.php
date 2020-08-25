<?php
class Settings_lookupeditor{
    function run($params){                   
        $allowedGroups = array('product_type','brand','material','ledger');
                                
        if(!isset($params['group'])||!in_array($params['group'],$allowedGroups)){
            trigger_error('Please specify a group / allowed group',E_USER_ERROR);
            exit();
        }    
        
        if($params['_do'] == 'add'){                       
            $id = Lookup::add($params['group'],$params['value']);
            self::addToParentDropdown($params['group'],$params['value'],$id);                 	        
        }
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
    function addToParentDropdown($group,$field,$value){
        
        
        $group = str_replace('"','\"',$group);
        $field = str_replace('"','\"',$field);
        $value = str_replace('"','\"',$value);
        
        exit('<script type="text/javascript">                                       
                window.parent.$.updateDropdown("'.$group.'","'.$field.'","'.$value.'");
                window.parent.$.fancybox.close();
              </script>');          
    }
    
}