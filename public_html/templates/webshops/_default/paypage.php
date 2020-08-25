<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
class Paypage{
    public static function run($params){         
		#?=15&=13
		#$params['relation_id']  = 15;
		#$params['payorder']  = 13;
		
        unset($_SESSION['basket_db']);

        Conversion::registerOrder($params['payorder']);
        
        $paymethod      = Paymethod::getById(ShoppingbasketDb::getPaymethod($params['payorder']));         
        $lowerPayMethod = strtolower($paymethod['name']);


        if(!in_array($lowerPayMethod,array('overboeking','rembours','contant of pin bij afhalen'))){
            $psp        = new Psp;       
            $redirect   = $psp->getRedirectUrl($params['payorder'],$params['relation_id'],$params['lang']);            
            if(!is_array($redirect)){            
                redirect($redirect);     
            }else
                exit($redirect['post_form']);                            
        }else{
            $redirects = array(
                'overboeking'                   => '/'.$params['lang'].'/overboeking.html',
                'rembours'                      => '/'.$params['lang'].'/rembours.html',
                'contant of pin bij afhalen'    => '/'.$params['lang'].'/contant.html'
            );                        
            redirect($redirects[$lowerPayMethod].sprintf('?payorder=%d',$params['payorder']));   
        }                       
        return $params;                                                
    }
}
