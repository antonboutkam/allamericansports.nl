<?php
class Settings_webshop_config{
    function  run($params){

        if(isset($params['_do']) && $params['_do']=='set_visible_section'){
            User::setSetting(User::getId(),'webshop_config_visible_section',$params['visible_section']);
        }
	    $params['visible_section'] = User::getSetting(User::getId(),'webshop_config_visible_section');
        $files              = glob('./classes/shippingcost/*');
        $params['shopname'] = Webshop::getWebshopById($params['webshop_id']);
                                
        foreach($files as $file){
            $aParts = explode(".",basename($file));
            $class  = ucfirst(array_shift($aParts));
            $obj    = new $class;
            $tmp    = array(    'class'     => $class,                                          
                                'desc'      => $obj->moduleDesc(),
                                'fields'    => $obj->configFields());
                                    
            $params['shipping_cost_calc_methods'][] = $tmp;                                                           
        }        
        if(isset($params['setting'])){
            foreach($params['setting'] as $setting=>$value){
                Webshop::setWebshopSetting($params['webshop_id'],$setting,$value);
            }
        }

        
        $settings           = Webshop::getWebshopSettings($params['webshop_id'],true);        

        $params             = array_merge($params,$settings);
        $params['content'] = parse('settings/settings_webshop_config',$params);
        return $params;
    }
}