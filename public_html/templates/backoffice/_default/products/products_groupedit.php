<?php
class Products_groupedit{
    function  run($params){
        if($params['_do']=='link_product' && $params['ajax']==1)                        
            ProductGroup::link($params['fk_catalogue'],$params['fk_product_group']);
        if($params['_do']=='unlink_product' && $params['ajax']==1)
            ProductGroup::unLink($params['link_id']);

    
        if($params['_do']=='store'){
            $filter[] = array('key'=>'group_name','value'=>'"'.$params['product_group']['group_name'].'"');
            $items    = ProductGroup::find(1,20,$filter);            
            if($items['rowcount']>1){
                $params['group_exists'] = 1;                
            }else{
                 ProductGroup::store($params['product_group'],$params['id']);
                 $params['name_changed'] = 1;             
            }
        }

        /*
        $params['id']
                    
        if($params['_do']=='delete_group' && $params['ajax']==1)
            ProductGroup::markDeleted($params['group_id']);            

#        pre_r($params);
          
        $filters[]                              = array('key'=>'is_deleted','value'=>'0');
        */
        
        $filters = array();
        $filters[]                              = array('key'=>'pg.id','value'=>$params['id']);        
        $fields                                 = ' GROUP_CONCAT(co.color) colors,
                                                    cpg.id cpgid,
                                                    c.article_number,
                                                    c.global_stock,
                                                    c.article_name,
                                                    cpg.*,
                                                    c.sale_price*1.'.Cfg::getPref('btw').' sale_price_vat';

        $params['current_page']                 = ($params['current_page'])?$params['current_page']:1;
        $params['products_groups_items']        = ProductGroup::findLinks($params['current_page'],20,$filters,$orderBy,$fields);
                              
        $params['product_group_products_tbl']   = parse('inc/product_group_products_tbl',$params);                              
        $params['products_group']               = ProductGroup::getById($params['id']);
        #pre_r($params['products_group']);
        $params['content']                      = parse('products_groupedit',$params);
        return $params;
    }
 
}