<?php
class Orders_new{
     function  run($params){
        $params['rand'] = rand(0,999999999);
        $params['btw'] = Cfg::getPref('btw');
        
        $params['languages'] = TranslateWebshop::getAllEnabledLanguages();
        
        if($params['_do']=='change_quantity'){
            $productId = OrderDao::getOrderIdByOrderItemId($params['orderItemId']);            
            $stock = StockDao::getProductStock($productId);
            $quantity = 0;
            if(is_array($stock))
                foreach($stock as $row)
                    $quantity = $row['quantity']+$quantity;
            if($quantity<$params['new_quantity']){
                $result['left_over'] = $quantity;
                $result['out_of_stock'] = true; 
            }else{
                $result['out_of_stock'] = false;
            }                        
            OrderDao::changeQuantity($params['orderItemId'],$params['new_quantity']);
            //exit(json_encode($result));
        }
        if($params['articleProperty']){
            $products              = ProductDao::find($params['articleProperty'],null,null,$params['current_page'],null,false);
            $params['products']    = $products['data'];
            $params['rowcount']    = $products['rowcount'];
        }
        if($_SESSION['orders']['orderid'] && !isset($params['orderid']))
            $params['orderid']              = $_SESSION['orders']['orderid']; 
        
        if($params['_do'] && $params['orderid']=='blank'){
            $params['orderid']              = OrderDao::createBlank();
            $_SESSION['orders']['orderid']  = $params['orderid'];
        }
        if($params['_do'] == 'cancel'){            
            OrderDao::removeOrder($params['orderid']);
            $_SESSION['orders']['orderid']  = null;
            $params['orderid']              = 'blank';
        } 
        
        
        if($params['orderdetail'])
            parse_str($params['orderdetail'],$details);
        if($params['_do']=='finalize'){            
            if($details['delivery']=='direct')
                OrderDao::directDelivery($params['orderid'],($params['paymenttookplace']==='true')?1:0);                                             
            OrderDao::acceptOrder($params['orderid'],($params['paymenttookplace']==='true')?1:0);  
        }
        if(in_array($params['_do'],array('finalize','park','offer'))){
            $details['fk_locale']   = $details['fk_locale'];   
            OrderDao::storeDetails($params['orderid'],$details);
            OrderDao::updateStock($params['orderid']);
        }
        if(in_array($params['_do'],array('finalize','park'))){

            $_SESSION['orders']['orderid']  = null;
            $params['done_orderid']         = $params['orderid']; 
            $params['orderid']              = 'blank';
        }                                                                      
        if($params['_do']=='del_orderitem')        
            OrderDao::removeOrderItem($params['orderid'],$params['orderitemid']);
            
        if($params['_do']=='add_product_by_barcode'){
            $params['productId'] = BarcodeDao::getProductIdByBarcode($params['barcode']);
            $params['_do']='add_product';
        }            
        if($params['_do']=='add_product')        
            OrderDao::addProduct($params['orderid'],$params['productId'],1);
            
        if($params['_do']=='add_client')
            OrderDao::addClient($params['orderid'],$params['clientid']);

        if($params['orderid'] && $params['orderid']!='blank'){            
            $order                      = OrderDao::getOrder($params['orderid']);            
            $order_items                = OrderDao::getOrderItems($params['orderid']);
            $params['order_items']      = $order_items['data'];            
            $params                     = array_merge($params,$order);
            $params['customer_info']    = parse('customer_info',$params,__FILE__);
        }
        
        $params['location_name']        = User::getLocationName();
        $params['orderid']              = ($params['orderid'])?$params['orderid']:'blank';
        $params['clientid']             = ($params['orderid'])?$params['orderid']:'blank';
        $params['current_page']         = (isset($params['current_page']))?$params['current_page']:1;
        $params['sort']                 = ($params['sort'])?$params['sort']:'article_number';                
        $params['order_items_tbl']      = parse('inc/order_items_tbl',$params);

        $params['discount_perc_cent']   = ($params['discount_perc_cent'])?$params['discount_perc_cent']:'00';
        $params['discount_perc_euro']   = ($params['discount_perc_euro'])?$params['discount_perc_euro']:'0';
        $params['discount_fixed_cent']  = ($params['discount_fixed_cent'])?$params['discount_fixed_cent']:'00';
        $params['discount_fixed_euro']  = ($params['discount_fixed_euro'])?$params['discount_fixed_euro']:'0';
        if($params['discount_perc_cent']>0||$params['discount_perc_euro'])
            $params['checkpercent'] = true;
        if($params['discount_fixed_cent']>0||$params['discount_fixed_euro']>0)
            $params['checkfixed'] = true;

        $params['pricetbl']             = parse('inc/pricetbl',$params);
        if($params['ajaxresult']){
            print json_encode($params);
            exit(); 
        } 
        $paymethods                     =   Paymethod::getAll('name');
        $params['paymethods']           =   $paymethods['data'];        
        $params['product_find_form']    =   parse('inc/product_find_form',$params);
        
        return $params;            
    }
	/*

    private static function clientSelector($params){
        $params['current_page']         = (isset($params['current_page']))?$params['current_page']:1;
        
        if($params['ajaxresult'])
            $params['query']            = ($params['query']==$params['defaultquery'])?'':$params['query'];
       
        $params['sort']                 = ($params['sort'])?$params['sort']:'company_name';        
        $relations                      = RelationDao::find($params['query'],null,$params['current_page'],$params['sort']);
            
        $params['relations']            = $relations['data'];
        $params['rowcount']             = $relations['rowcount']; 
        $params['paginate']             = paginate($params['current_page'],$params['rowcount']);

        $params['order_relation_tbl']   = parse('order_relation_tbl',$params,__METHOD__);
        
        if($params['ajaxresult']){
            print $params['order_relation_tbl'];
            exit(); 
        }
                       
        return $params;
    } 
	*/
        

}
