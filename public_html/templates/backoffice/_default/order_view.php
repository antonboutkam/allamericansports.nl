<?php
class Order_view{
     function  run($params){                                               
        $order                          = OrderDao::getOrder($params['orderid']);        
        $order_items                    = OrderDao::getOrderItems($params['orderid']);
        $params['order_items']          = $order_items['data'];            
        $params                         = array_merge($params,$order);
        $params['customer_info']        = parse('inc/customer_info',$params);        
        $params['orderid']              = ($params['orderid'])?$params['orderid']:'blank';
        $params['clientid']             = ($params['orderid'])?$params['orderid']:'blank';
        $params['current_page']         = (isset($params['current_page']))?$params['current_page']:1;
        $params['sort']                 = ($params['sort'])?$params['sort']:'article_number';        
        $params['order_view_tbl']       = parse('inc/order_view_tbl',$params);                
        return $params;            
    }
}