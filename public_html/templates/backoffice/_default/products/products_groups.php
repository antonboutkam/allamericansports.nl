<?php
class Products_groups{
    function  run($params){
        if($params['_do']=='add_group'){            
            $group_exists =  ProductGroup::getBy('group_name',$params['product_group']['group_name']);                        
            if(!isset($group_exists['id'])){
                $params['new_group_id'] = ProductGroup::store($params['product_group'],'new');
                $params['group_saved'] = 1;
            }else
                $params['group_exists'] = 1; 
            
        }                    
        if($params['_do']=='delete_group' && $params['ajax']==1)
            ProductGroup::markDeleted($params['group_id']);            

        $orderBy                        = 'group_name';
        $filters[]                      = array('key'=>'is_deleted','value'=>'0');
        if($params['query']){
            $filters[]                  = array('key'=>'(pg.id','value'=>sprintf('"%%%1$s%%" OR group_name LIKE  "%%%1$s%%")',$params['query']),'operator'=>' LIKE ');
            #$filters[]                  = array('key'=>'group_name','value'=>sprintf('"%%%s%%"',$params['query']),'operator'=>' LIKE ');
        }
        $params['current_page']         = (isset($params['current_page']))?$params['current_page']:1;
        $params['products_groups']      = ProductGroup::find($params['current_page'],20,$filters,$orderBy,'pg.*');
        
        $params['product_groups_tbl']   = parse('inc/product_groups_tbl',$params);                
        $params['content']              = parse('products_groups',$params);
        return $params;
    }
 
}