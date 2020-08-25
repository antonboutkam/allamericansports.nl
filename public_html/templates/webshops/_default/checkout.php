<?php
class Checkout{
    
    function run($params){


        if($params['_do'] == 'get_address'){
            // You have over here: $params['billing_number'] and $params['billing_postal']
            $number=$params['billing_number'];
            $post_code=$params['billing_postal']; 
            
            if($post_code){
                if($number){
                   $uri = 'http://api.postcodeapi.nu/'.$post_code.'/'.$number; 
                }else{                  
                    $uri = 'http://api.postcodeapi.nu/'.$post_code;
                }
            
            }
            $ch = curl_init($uri);
            $header  = array("Content-type: application/PTI26application/x-www-form-urlencoded; charset=UTF-8",
              "Accept: */*",
              "Accept-Language: en",
              "Api-Key: a7927fe441579a299e532c7f2a586e04c45db0db");
        

            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            $out = curl_exec($ch);
            curl_close($ch);
            exit($out);            
        }
        /*  
            Stappen
            0 checkout_empty.html 
         >  1 checkout_myaccount.html,
            2 checkout_sendmethod.html,            
            3 checkout_paymethod.html,            
            4 checkout_final.html.html 
        */      
    	$params['title_override']       = 'AFREKENEN - REGISTRATIE';               
        $params['extra_js_file'][]      = 'checkout.validation.jquery.js';
        $params['extra_js_file'][]      = 'login.jquery.js';
        $params                         = Webshop::doFirst($params);
        
        if($_SESSION['basket_db'])
            $basket = ShoppingbasketDb::getBasket($_SESSION['basket_db']);    
        else
            $basket = Shoppingbasket::getBasket();

        if(empty($basket))
            redirect('/'.$params['lang'].'/checkout_empty.html');
                        
        if($params['_do']=='login'){
            $params = self::login($params);
        }            
        
        if($params['_do']=='create_account'){        
            $root = $params['root'].'/'.$params['lang'];
            $params = self::store($params);                    
            redirect($root.'/checkout_sendmethod.html');                                    
        }    
        
        $params['relation'] = RelationDao::getMember();
        #pre_r($params['relation']);
        if((empty($params['relation']['shipping_street']) && 
            empty($params['relation']['shipping_number']) && 
            empty($params['relation']['shipping_postal']) && 
            empty($params['relation']['shipping_city']))){
            $params['same_as_billing'] = true;
        }
        $params['country_iso']      = Countryiso::getAll();
        $params['shipping_form']    = parse('inc/shipping_form',$params);
        $params['content']  = parse('checkout_myaccount',$params);
        return $params;                                               
    }
    private static function login($params){
        $credentialsOk = RelationDao::login($params['emailaddr'],$params['password']);
        if(!$credentialsOk){        
            $params['login_failed'] = !$credentialsOk;
        }else{
            $params['is_member'] = 1; 
        }
        return $params;                    
    }
    private function store($params){        
        $params['client_new']['webshop'] = $params['current_webshop_id'];
        
        $relation_id = RelationDao::store($params['client_new'], RelationDao::getMemberId());    
        // Gegevens / wijzigingen reloaden in sessie geheugen,        
        $_SESSION['relation']= RelationDao::getById($relation_id);
    }               
}