<?php
class Checkout{
    
    function run($params){               
        // Bestaande order opnieuw initieren\                            
        if(isset($params['payorder']) && OrderDao::orderBelongsToRelation($params['payorder'],RelationDao::getMemberId())){
            $_SESSION['basket_db'] = $params['payorder'];
            // Eerdere betaling is fout gegaan dus opnieuw naar betaalmethode vragen.
            unset($_SESSION['paymethod_set']);
            // de-anuleren (order was eerder gecanelled, nu un-cancellen)      
            OrderDao::markCancelled($_SESSION['basket_db'],RelationDao::getMemberId(),0);                                                                                                   
        }        
        if($params['_do']=='login'){
            $credentialsOk = RelationDao::login($params['emailaddr'],$params['password']);
            if(!$credentialsOk)
                $params['login_failed'] = !$credentialsOk;
        }  
              
        if($params['_do']=='create_account'){            
            $params['relation']['type'] = 'prospect';
            $params['relation_id']      = RelationDao::store($params['client_new'], null);
            RelationDao::loginById($params['relation_id']);
        }    
        $params['title_override']       =  'Afrekenen';                         
        $params['relation_is_member']   = RelationDao::isMember(); 
           
        if($params['_do']=='complete'){            
            self::completeOrder($params);
        }
        if($params['clear']=='delivery')
            unset($_SESSION['delivery_set']);
        if($params['clear']=='paymethod')
            unset($_SESSION['paymethod_set'],$_SESSION['delivery_set']);
        if($params['clear']=='paymethod_only')
            unset($_SESSION['paymethod_set']);                        
        if($params['clear']=='asked_add_wire')        
            unset($_SESSION['paymethod_set'],$_SESSION['delivery_set'],$_SESSION['asked_add_wire']);                    
            
        if(isset($params['delivery'])&& RelationDao::isMember()){
            self::setOrderDelivery($params['delivery'],$params['hostname']);
            $_SESSION['delivery_set'] = 1;
        }            
        if($_SESSION['basket_db']){
            $basket = ShoppingbasketDb::getBasket($_SESSION['basket_db']);    
        }else{   
            $basket = Shoppingbasket::getBasket();
        }		
                        
        $params['extra_js_file'][]      = 'checkout.validation.jquery.js';
        $params['extra_js_file'][]      = 'login.jquery.js';
           
        
        $params             = Webshop::doFirst($params);                  
        if(empty($basket)){
            unset($_SESSION['basket_db']);
            $params['content'] = parse('checkout_empty',$params);
            return $params;
        }
        if(!$params['netsnoer_nodig'] = self::wireNeeded($basket)){
            $_SESSION['asked_add_wire'] = true;
        }      
        $params['title_override'] = 'Afrekenen';
        if(isset($params['add_wire'])){            
            if($params['add_wire']){
                if($_SESSION['basket_db']){
                    ShoppingbasketDb::addProduct($_SESSION['basket_db'],386,1);
                }else{
                    Shoppingbasket::addProduct(386,1);
                }                                    
            }                                         
            $_SESSION['asked_add_wire'] = true;            
        }
            
        $params['ideal_banks']          = Ideal::getBanks();
         
        
        if($params['paymethod']){
            $_SESSION['paymethod_set'] = true;
            if($params['paymethod']==1){
                $bank = self::getBank($params['ideal_banks'],$params['payment']['bank']);                                                
                Shoppingbasket::setBank($bank);                                
            }else{
                Shoppingbasket::setBank(null);
            }
            if($_SESSION['basket_db']){
                ShoppingbasketDb::setPaymethod($params['paymethod'],$_SESSION['basket_db']);
            }else{
                Shoppingbasket::setPaymethod($params['paymethod']);
            }                
        }
        $params['session']              = &$_SESSION;                                    
        $params['country_iso']          = Countryiso::getAll();
        $params['shipping_form']        = parse('inc/shipping_form',$params);
                        
        if(!RelationDao::isMember()){            
            $params['content']  = parse('checkout_step1',$params);            
        }else if(RelationDao::isMember() && !$_SESSION['asked_add_wire']){
            $params['content']  = parse('checkout_step2',$params);
        }else if(RelationDao::isMember() && $_SESSION['asked_add_wire'] && !$_SESSION['paymethod_set']){
            $paymethods             = Paymethod::getAll('name',true);    
            $params['webshop']      = Webshop::getIdByWebshop($params['hostname']);
            // Default paymethod instellen
            $params                 = array_merge(array('paymethod'=>1),$params);
            $params['paymethods']   = number_format_array($paymethods['data'],'price_amount',2,",",".");                              
            $params['content']      = parse('checkout_step3',$params);
        }else if(RelationDao::isMember() && $_SESSION['asked_add_wire'] && $_SESSION['paymethod_set'] && !$_SESSION['delivery_set']){
            // Verzendkosten uitrekeken als de klant voor verzending kiest.
            $currentSetting = Shoppingbasket::hasDelivery();            
            if($_SESSION['basket_db']){   
                ShoppingbasketDb::setDelivery($params['hostname'],$_SESSION['basket_db'],$currentSetting,false);
                $params['calc_send_cost'] =  ShoppingBasketDb::getDeliveryPrice($_SESSION['basket_db'],true,true);
            }else{
                Shoppingbasket::setDelivery($params['hostname'],$currentSetting,false);
                $params['calc_send_cost'] =  Shoppingbasket::getDeliveryPrice(true,true);
            }                        
            
            $params['content']      = parse('checkout_step4',$params);
        }else if(RelationDao::isMember() && $_SESSION['asked_add_wire'] && $_SESSION['paymethod_set'] && $_SESSION['delivery_set'] ){            
            $params                 = self::getOrderData($params);
            $params['content']      = parse('checkout_step5',$params);
        }else{            
            trigger_error(__METHOD__.' Script ended during checkout, no route set.',E_USER_ERROR);
        }        
        return $params;
    }
    private static function completeOrder($params){
            if($_SESSION['basket_db']){  
                $tmpPaymethod = ShoppingBasketDb::getPaymethod($_SESSION['basket_db']);
                $basket       = ShoppingBasketDb::getBasket($_SESSION['basket_db']);            
            }else{
                $tmpPaymethod = Shoppingbasket::getPaymethod();
                $basket       = Shoppingbasket::getBasket();                                                                                              
                $paymethod    = Paymethod::getById($tmpPaymethod);
                $relation     = RelationDao::getMember();
                $orderid      = OrderDao::createBlank($params['hostname']);                

                if(is_array($basket)){
                    foreach($basket as $item){
                        if(isset($orderid) && is_numeric($orderid)){
                            OrderDao::addProduct($orderid,$item['id'],$item['order_quantity']);
                        }else{
                            trigger_error(__METHOD__.' Order not set where it should',E_USER_WARNING);
                        }
                    }    
                }
                
                // Add paymethod  
                $data['payment']['method'] = $tmpPaymethod; // Laten staan, voor OrderDao::storeDetails          
                OrderDao::storeDetails($orderid,$data);
                // End add paymethod
                
                
                OrderDao::addClient($orderid,$relation['id']);            
                // Automatically uses the main warehouse id.
                OrderDao::updateStock($orderid);
            }
                                        
            if($orderid){
                $_SESSION['basket_db'] = $orderid;
            }
            Mailer::sendOrderMail($params,$_SESSION['basket_db']);                      

            if($paymethod['name']=='Contant'){
                redirect(sprintf('%s/pagina/contant-betalen.html',$params['root']));                
            }else if($paymethod['name']=='Rembours'){
                redirect(sprintf('%s/pagina/rembours-betalen.html',$params['root']));
            }else if($paymethod['name']=='Overboeking'){
                redirect(sprintf('%s/pagina/overboeken.html',$params['root']));
            }else{
                $url = sprintf('%s/paypage.php?relation_id=%s&payorder=%s&payment=1',$params['root'],$relation['id'],$_SESSION['basket_db']);
                redirect($url);                
            }            
    }
    private static function getBank($ideal_banks,$bankId){
        if(empty($ideal_banks) || !is_array($ideal_banks))
            return;
        foreach($ideal_banks as $bank)
            if($bank['id']==$bankId)
                return $bank;
    }
    private static function getOrderData($params){        
        $params['relation']                         = RelationDao::getMember();
         if($_SESSION['basket_db']){  
            $params['cart']['basket']                   = ShoppingbasketDb::getBasket($_SESSION['basket_db']);
            $params['cart']['subtotal']                 = ShoppingbasketDb::getSubtotal($_SESSION['basket_db']);
            $params['cart']['delivery']                 = ShoppingbasketDb::getDeliveryPrice($_SESSION['basket_db'],true);
    		$params['cart']['has_delivery']             = (bool)ShoppingbasketDb::getDeliveryPrice($_SESSION['basket_db'],false);
            $params['cart']['total']                    = ShoppingbasketDb::getTotal($_SESSION['basket_db']);
            $params['cart']['vat']                      = ShoppingbasketDb::getVat($_SESSION['basket_db']);
            $params['cart']['transaction_cost_type']    = ShoppingbasketDb::getPaymethodCalctype($_SESSION['basket_db']);            
            $params['cart']['transaction_fee']          = ShoppingbasketDb::getTransactionFeeVis($_SESSION['basket_db']);
            $params['cart']['transaction_value']        = ShoppingbasketDb::getPaymethodValue($_SESSION['basket_db']);
            $params['cart']['paymethod']                = ShoppingbasketDb::getPaymethod($_SESSION['basket_db']);          
        }else{            
            $params['cart']['basket']                   = Shoppingbasket::getBasket();
            $params['cart']['subtotal']                 = Shoppingbasket::getSubtotal();
            $params['cart']['delivery']                 = Shoppingbasket::getDeliveryPrice(true);
    		$params['cart']['has_delivery']             = (bool)Shoppingbasket::getDeliveryPrice(false);
            $params['cart']['total']                    = Shoppingbasket::getTotal();
            $params['cart']['vat']                      = Shoppingbasket::getVat();
            $params['cart']['transaction_cost_type']    = Shoppingbasket::getPaymethodCalctype();
            $params['cart']['transaction_fee']          = Shoppingbasket::getTransactionFeeVis();
            $params['cart']['transaction_value']        = Shoppingbasket::getPaymethodValue();
            $params['cart']['paymethod']                = Shoppingbasket::getPaymethod();             
        }
        $params['cart']['has_transaction_cost']     = (bool)(float)$params['cart']['transaction_fee'];
        $params['cart']['paymethod_vis']            = Paymethod::getById($params['cart']['paymethod']);
        // Voor get Bank gebruiken we gewoon ShoppingBasket, de bank wordt niet in de database opgeslagen.
        $params['cart']['bank']                     = Shoppingbasket::getBank();

        return $params;  
    }
    private static function setOrderDelivery($delivery,$hostname){
        $relation = RelationDao::getMember();
        if($relation['same_as_billing']==1){            
            $internationalOrder = ($relation['billing_country']=='Nederland')?0:1;
        }else{
            $internationalOrder = ($relation['shipping_country']=='Nederland')?0:1;
        }  
        if($_SESSION['basket_db']){ 
            ShoppingbasketDb::setDelivery($hostname,$_SESSION['basket_db'],$delivery,$internationalOrder);
        }else{
            Shoppingbasket::setDelivery($hostname,$delivery,$internationalOrder);    
        }      
    }
    private static function wireNeeded($basket){        
        foreach($basket as $item)
            if(in_array($item['product_type'],array('Lader','Originele lader')))
                return true;                
        return;
    }
    
}