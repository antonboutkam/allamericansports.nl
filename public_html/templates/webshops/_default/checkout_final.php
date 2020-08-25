<?php
class Checkout_final{    
    function run($params){        
        /*  
            Stappen
            0 checkout_empty.html 
            1 checkout_myaccount.html,
            2 checkout_sendmethod.html,            
            3 checkout_paymethod.html,            
         >  4 checkout_final.html.html 
        */                          
        
        $params['has_delivery_set'] = $_SESSION['has_delivery_set']?true:false;
              
        if(!RelationDao::isMember())
            redirect(sprintf('%s/login.html?r=/checkout.html',$params['root']));                            
        $params['title_override']       =   'AFREKENEN - CONTROLE';
        $params                         =   Webshop::doFirst($params);        
        $basket                         =   self::getBasket($_SESSION['basket_db']);                                                                                                                                                        
        if(empty($basket))
            redirect('%s/checkout_empty.html',$params['root']);


        if($params['_do']=='complete') {             
            self::completeOrder($params);                              
        }
        
        
        $params              =  self::getOrderData($params);
        
        if($params['lang']=='gb')
            $params['cart']['paymethod_vis']['name'] = str_replace('Contant of Pin bij afhalen','Cash, pay at pickup',$params['cart']['paymethod_vis']['name']);
        
        $params['content']   =  parse('checkout_final',$params);#pre_r($params);
        return $params;                                               
    }
    private static function completeOrder($params){
        
        #echo "BASKET DB IS ".$_SESSION['basket_db']."<br>";
        #echo "Paymethod non basket db is ".Shoppingbasket::getPaymethod()."<br>";
        #echo "Paymethod basket db is ".ShoppingBasketDb::getPaymethod($_SESSION['basket_db'])."<br>";
        ini_set('display_errors',1);
        $basket = self::getBasket($_SESSION['basket_db']);
        
        if($_SESSION['basket_db']){  
            $orderid = $_SESSION['basket_db'];
            $tmpPaymethod = ShoppingBasketDb::getPaymethod($_SESSION['basket_db']);           
            if(!$tmpPaymethod && Shoppingbasket::getPaymethod()){
                // Er was geen paymethod in de database opgeslagen terwijl de order al wel in de database staat.
                // Deze situatie is uitzonderlijk maar dit kan voorkomen door vreemde navigatie of bij debuggen.
                $tmpPaymethod = Shoppingbasket::getPaymethod();
            }
        }else{            
            
            $tmpPaymethod = Shoppingbasket::getPaymethod();                                                                                               
            
            $relation     = RelationDao::getMember();            
            $orderid      = OrderDao::createBlank($params['hostname']);            
                      
            self::insertOrderItemsIntoDb($basket,$orderid);                                         
            OrderDao::addClient($orderid,$relation['id']);            
            // Automatically uses the main warehouse id.
            OrderDao::updateStock($orderid);
        }
        #_d($params);
        #exit('current fk locale'.$params['current_fk_locale']);
        $storeData              = array('fk_locale'=>$params['locale'],'payment'=>array('method'=>$tmpPaymethod,'note'=>$params['personal_note'],'terms'=>$params['agree_terms']));
        $storeData['send_cost'] = Shoppingbasket::getDeliveryPrice(true);                
        OrderDao::storeDetails($orderid,$storeData,$params['hostname']);                
        
        $sql = sprintf('UPDATE orders SET discount_fixed=%s WHERE id=%d',Shoppingbasket::getDiscount(),$orderid);
                                               
        $paymethod    = Paymethod::getById($tmpPaymethod);
        if($orderid)
            $_SESSION['basket_db'] = $orderid;
        
        
        
        Mailer::sendOrderMail($params,$_SESSION['basket_db']);                     
        redirect(sprintf('%s/%s/paypage.php?relation_id=%s&payorder=%s',$params['root'],$params['lang'],$relation['id'],$orderid));                                
    }    
    private static function getBasket($orderId){
        if($orderId)
            return ShoppingbasketDb::getBasket($orderId);    
        return Shoppingbasket::getBasket();                
    }
    private static function insertOrderItemsIntoDb($basket,$orderid){
        if(is_array($basket))
            foreach($basket as $item)
                if(isset($orderid) && is_numeric($orderid))
                    OrderDao::addProduct($orderid,$item['id'],$item['order_quantity']);
                else
                    trigger_error(__METHOD__.' Order not set where it should',E_USER_WARNING);                                       
    }
  
    private static function getOrderData($params){     
        $params['relation']                         = RelationDao::getMember();

        $params['same_as_billing'] = false;
        if((empty($params['relation']['shipping_street']) && 
            empty($params['relation']['shipping_number']) && 
            empty($params['relation']['shipping_postal']) && 
            empty($params['relation']['shipping_city']))){
            $params['same_as_billing'] = true;
        }    
        ini_set('display_errors',1);
        $params['cart']['basket']                   = self::getBasket($_SESSION['basket_db']);
        
        if(!empty($params['cart']['basket'])){
            foreach($params['cart']['basket'] as &$orderItem){
                $colors     =  ColorDao::getProductColors($orderItem['id']);
                $joinColors = array();
                if(!empty($colors)){
                    foreach($colors as $color){
                        $joinColors[] = $color['color'];
                    }
                }
                $orderItem['colors']        = join(',',$joinColors);             
            }
        }        
        
        
        
         if($_SESSION['basket_db']){              
            $params['cart']['subtotal']                 = ShoppingbasketDb::getSubtotal($_SESSION['basket_db']);
            $params['cart']['delivery']                 = ShoppingbasketDb::getDeliveryPrice($_SESSION['basket_db'],true);
            $params['cart']['delivery_vat']             = ShoppingbasketDb::getDeliveryPrice(true,true);
            $params['cart']['has_delivery']             = (bool)ShoppingbasketDb::getDeliveryPrice($_SESSION['basket_db'],false);
            $params['cart']['total']                    = ShoppingbasketDb::getTotal($_SESSION['basket_db']);
            $params['cart']['vat']                      = ShoppingbasketDb::getVat($_SESSION['basket_db']);
            $params['cart']['transaction_cost_type']    = ShoppingbasketDb::getPaymethodCalctype($_SESSION['basket_db']);            
            $params['cart']['transaction_fee']          = ShoppingbasketDb::getTransactionFeeVis($_SESSION['basket_db']);
            $params['cart']['transaction_value']        = ShoppingbasketDb::getPaymethodValue($_SESSION['basket_db']);
            $params['cart']['paymethod']                = ShoppingbasketDb::getPaymethod($_SESSION['basket_db']);                   
        }else{                        
            $params['cart']['subtotal']                 = Shoppingbasket::getSubtotal();
            $params['cart']['delivery']                 = Shoppingbasket::getDeliveryPrice(true);
            $params['cart']['delivery_vat']             = Shoppingbasket::getDeliveryPrice(true,true);
            $params['cart']['has_delivery']             = (bool)Shoppingbasket::getDeliveryPrice(false);
            $params['cart']['total']                    = Shoppingbasket::getTotal();
            
            $params['cart']['vat']                      = Shoppingbasket::getVat();
            $params['cart']['transaction_cost_type']    = Shoppingbasket::getPaymethodCalctype();
            $params['cart']['transaction_fee']          = Shoppingbasket::getTransactionFeeVis();
            $params['cart']['transaction_value']        = Shoppingbasket::getPaymethodValue();
            $params['cart']['paymethod']                = Shoppingbasket::getPaymethod();                  
            $params['cart']['discount']                 = number_format((float)Shoppingbasket::getDiscount(),2,",",".");
            $params['cart']['discount_props']           = Shoppingbasket::getDiscountProps();
            
        }           
        $params['cart']['has_discount']             = empty($params['cart']['discount_props'])?0:1;
        
        $params['cart']['has_transaction_cost']     = (bool)(float)str_replace(',','.',$params['cart']['transaction_fee']);                
        $params['cart']['paymethod_vis']            = Paymethod::getById($params['cart']['paymethod']);
        // Voor get Bank gebruiken we gewoon ShoppingBasket, de bank wordt niet in de database opgeslagen.
        $params['cart']['bank']                     = Shoppingbasket::getBank();

        return $params;  
    }            
}    
