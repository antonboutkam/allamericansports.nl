<?php
class Checkout_paymethod{    
    function run($params){           
        #exit();
        /*  
            Stappen
            0 checkout_empty.html 
            1 checkout_myaccount.html,
            2 checkout_sendmethod.html,            
         >  3 checkout_paymethod.html,            
            4 checkout_final.html.html 
        */
        $psp = new Psp();

        $params['has_delivery'] = self::hasDelivery($_SESSION['basket_db']);
        if(!RelationDao::isMember())
            redirect('/login.html?r=/checkout_paymethod.html');                    
        $params['title_override']       =   'AFREKENEN - BETAALMETHODE';
        $params['extra_js_file'][]      =   'checkout.jquery.js';
        $params                         =   Webshop::doFirst($params);        
        $basket                         =   self::getBasket($_SESSION['basket_db']);                                                                                                                                                        
                
        if(empty($basket))
            redirect($params['root'].'/'.$params['lang'].'/checkout_empty.html');
        $paymethods                     = Paymethod::getAll('name',true,$_SESSION['has_delivery_set']);        
        #pre_r($_SESSION);
        //$params['has_delivery']        
        if($paymethods['rowcount']==1){
            if($_SESSION['basket_db'])
                ShoppingbasketDb::setPaymethod($paymethods['data'][0]['id'],$_SESSION['basket_db']);
            else
                Shoppingbasket::setPaymethod($paymethods['data'][0]['id']);             
           redirect('/checkout_final.html'); 
        }

        $vat = '1.'.Cfg::getPref('btw');
        
        
        foreach($paymethods['data'] as $id => $paymethod){         
            if($paymethod['postorder_paymethod']==0 && Shoppingbasket::hasDelivery()==1){
                unset($paymethods['data'][$id]);
                continue;
            }
            $paymethods['data'][$id]['price_amount'] = $paymethod['price_amount'] * (float)$vat;        
            if($paymethods['data'][$id]['name']=='Contant of Pin bij afhalen' && $params['lang']=='gb'){
               $paymethods['data'][$id]['name'] = 'Cash, pay at pickup'; 
            }    
            if($paymethods['data'][$id]['price_amount']=='16.9884'){
                $paymethods['data'][$id]['price_amount'] = 17;
            }                         
        }
        
        //$vat
                                        
        $params['paymethods']           = number_format_array($paymethods['data'],'price_amount',2,",",".");                   
        
        #pre_r($params);
        if($params['lang']=='gb'){
            foreach($params['paymethods'] as $key => $val){
                if(strtolower($val['name']) == 'overboeking')
                    $params['paymethods'][$key]['name'] = 'Bank transfer';
                
                if(strtolower($val['name']) == 'rembours')
                    $params['paymethods'][$key]['name'] = 'Cash on delivery, only in the Netherlands';
                
            }
        }

        if($params['pmethod']){     
            #exit('Yow');
            /*
            if($params['paymethod']==1){
                $bank = self::getBank($params['ideal_banks'],$params['payment']['bank']);                                                
                Shoppingbasket::setBank($bank);    
            }else
                Shoppingbasket::setBank(null);                            
             * 
             */
            if($_SESSION['basket_db'])
                ShoppingbasketDb::setPaymethod($params['pmethod'],$_SESSION['basket_db']);
            else{
                Shoppingbasket::setPaymethod($params['pmethod']);                            
            }    
            redirect($params['root'].'/'.$params['lang'].'/checkout_final.html');
        }                                                                                   
        $params['content']   =   parse('checkout_paymethod',$params);
        return $params;                                               
    }
    private static function getBank($ideal_banks,$bankId){
        if(empty($ideal_banks) || !is_array($ideal_banks))
            return;
        foreach($ideal_banks as $bank)
            if($bank['id']==$bankId)
                return $bank;
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