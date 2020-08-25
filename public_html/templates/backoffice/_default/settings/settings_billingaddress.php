<?php
class Settings_billingaddress{
    function  run($params){
        $params['saved'] = false;
        if($params['_do']=='store'){
            Cfg::storePref('billing_address_nl', $params['billing_address_nl']);
            Cfg::storePref('billing_address_gb', $params['billing_address_gb']);            
            Cfg::storePref('billing_footer_nl', $params['billing_footer_nl']);
            Cfg::storePref('billing_footer_gb', $params['billing_footer_gb']);
            Cfg::storePref('billing_credit', $params['billing_credit']);
            Cfg::storePref('billing_thankyouorder_nl', $params['billing_thankyouorder_nl']);
            Cfg::storePref('billing_thankyouorder_gb', $params['billing_thankyouorder_gb']);                      
            $params['saved'] = true;
        }
        if($params['_do']=='store_settings')
            foreach($params['settings'] as $key=>$val){
                Cfg::storePref($key, $val);        
                $params['saved'] = true;                
            }
        
        $params['settings']        = Cfg::getPrefs();
        return $params;
    }
}