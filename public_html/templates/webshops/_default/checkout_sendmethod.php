<?php
class Checkout_sendmethod{    
    function run($params){        
        /*  
            Stappen
            0 checkout_empty.html 
            1 checkout_myaccount.html,
         >  2 checkout_sendmethod.html,            
            3 checkout_paymethod.html,            
            4 checkout_final.html.html 
        */
    
                                
        $params['international'] = self::isInternational();                
        
        $_SESSION['has_delivery_set'] = $params['new_delivery'];
        #exit($params['has_delivery_set']);
        if($params['_do']=='set_delivery'){ 
			self::setDelivery($params['hostname'],$params['new_delivery'],$params['international'],$_SESSION['basket_db']);                        
            redirect($params['root'].'/'.$params['lang'].'/checkout_paymethod.html');
        }   
        
        $params['delivery_price']       = self::calcDeliveryPrice($params['hostname'],$_SESSION['basket_db'],$params['international']);                        
        $params['delivery_on']          = (string)(int)self::hasDelivery($_SESSION['basket_db']);
                
        if(!RelationDao::isMember())
            redirect('/login.html?r=/checkout_paymethod.html');                    
        $params['title_override']       =   'AFREKENEN - VERZENDMETHODE';
        $params['extra_js_file'][]      =   'checkout.jquery.js';
        $params                         =   Webshop::doFirst($params);        
        $basket                         =   self::getBasket($_SESSION['basket_db']);                                                                                                                                                        
        if(empty($basket))
            redirect('/checkout_empty.html');

        Shoppingbasket::setDelivery($params['hostname'],$_SESSION['delivery_yn'],$_SESSION['international_order']);                    
        $params['delivery_price']  = Shoppingbasket::getDeliveryPrice(1,1);                                                                                     
        $params['content']   =   parse('checkout_sendmethod',$params);
        return $params;                                               
    }
    private static function setDelivery($hostname,$has_delivery,$internationalOrder,$orderId){
        if($orderId)
            return ShoppingbasketDb::setDelivery($hostname,$orderId,$has_delivery,$internationalOrder);
        return Shoppingbasket::setDelivery($hostname,$has_delivery,$internationalOrder);                     
    }
    private static function isInternational(){
        $params['relation'] = RelationDao::getMember();
        if($params['relation']['shipping_country'] && $params['relation']['shipping_country']!='Nederland'){
            return true;
        }else if($params['relation']['billing_country']!='Nederland'){
            return true;
        }
        return false;
    }
    
    private static function calcDeliveryPrice($hostname,$orderId,$internationalOrder){        
        // 1. delivery yes instellen
        // 2. delivery price ophalen
        // 3. delivery instellen op initiele waarde
        $had_delivery = self::hasDelivery($orderId);
        if($orderId){            
            #$had_delivery = (bool)(float)str_replace(',','.',ShoppingbasketDb::getDeliveryPrice($orderId));
            ShoppingbasketDb::setDelivery($hostname,$orderId,1,$internationalOrder);
            $delivery_price = Shoppingbasket::getDeliveryPrice(true,true);
            ShoppingbasketDb::setDelivery($hostname,$orderId,$had_delivery,$internationalOrder);    
        }else{            
            #$had_delivery = Shoppingbasket::hasDelivery();
            Shoppingbasket::setDelivery($hostname,1,$internationalOrder);
            $delivery_price = Shoppingbasket::getDeliveryPrice(true,true);
            Shoppingbasket::setDelivery($hostname,$had_delivery,$internationalOrder); 
        }            
        return $delivery_price;                
    }
    private static function hasDelivery($orderId){
        if($orderId)
            return (bool)(float)str_replace(',','.',ShoppingbasketDb::getDeliveryPrice($orderId));    
        return Shoppingbasket::hasDelivery();
    }
    private static function getBasket($orderId){
        if($orderId)
            return ShoppingbasketDb::getBasket($orderId);    
        return Shoppingbasket::getBasket();                
    }    
}    