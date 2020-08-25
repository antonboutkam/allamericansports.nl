<?php
class Products_discountgroups{
    function  run($params){
        if($params['_do']=='add_group'){            
            $group_exists =  Productdiscountgroup::getBy('group_name',$params['product_group']['group_name']);                        
            if(!isset($group_exists['id'])){
                $params['new_group_id'] = Productdiscountgroup::store($params['product_group'],'new');
                $params['group_saved'] = 1;
            }else
                $params['group_exists'] = 1; 
            
        }                    
        if($params['_do']=='delete_group' && $params['ajax']==1)
            Productdiscountgroup::markDeleted($params['group_id']);            

        $orderBy                        = 'group_name';
        $filters[]                      = array('key'=>'is_deleted','value'=>'0');
        if($params['query'])
            $filters[]                  = array('key'=>'group_name','value'=>sprintf('"%%%s%%"',$params['query']),'operator'=>' LIKE ');
        $params['current_page']         = (isset($params['current_page']))?$params['current_page']:1;
        $params['products_groups']      = Productdiscountgroup::find($params['current_page'],20,$filters,$orderBy,'*');
        
        $params['product_groups_tbl']   = parse('inc/product_discountgroups_tbl',$params);                
        $params['content']   =   parse('products_discountgroups',$params);
        return $params;
    }
 
}