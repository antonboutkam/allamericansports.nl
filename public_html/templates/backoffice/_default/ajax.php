<?php
class Ajax{
    function run($params){
 
        if($params['articleProperty']){
                $prevItemsPP = Cfg::get('items_pp');     
                $showwostock = ($params['showwostock']=='false')?false:true;           
                $tmp = ProductDao::find($params['articleProperty'],'article_number','article_number',1,1,$showwostock);                
                $params['rowcount'] = $tmp['rowcount'];                
                if($params['rowcount']==1){
                    $params['productId']     = $tmp['data'][0]['id'];
                    $params['articleNumber'] = $tmp['data'][0]['article_number'];
                }                                                    
        }           
        if($params['_do']=='pay_order')
            OrderDao::markPaid(USer::getId(),$params['orderid']);
        if($params['_do']=='sendsummation')
            Summate::send($params);
        
        if($params['_do']=='get_submenu'){
            $out['id'] = $params['id'];
            $out['data'] = Webshop::getProductMenuStructures($params['id'],$params['product_id'],true);                    
            exit(json_encode($out));
        }        
        if($params['_do']=='add_menu_item_recursive'){
            Webshop::addMenuRecursiveDown($params['menu_item'],$params['product_id']);
            Webshop::addRecursiveUp($params['menu_item'],$params['product_id']);            
        }    
        if($params['_do']=='remove_menu_item_recursive_down')
            Webshop::removeMenuRecursiveDown($params['menu_item'],$params['product_id']);                     
        print json_encode($params);                
        exit();
    }
}