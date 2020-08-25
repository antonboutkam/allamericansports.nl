<?php

class Settings_webshop_design {
    public static function run($params){
        if(isset($params['setting']))
            foreach($params['setting'] as $setting=>$value){
                //echo $params['webshop_id'],$setting.','.$value."<br>"
                Webshop::setWebshopSetting($params['webshop_id'],$setting,$value);
            }        
        $params['settings'] = Webshop::getWebshopSettings($params['webshop_id']);                  
        $params['webshop'] = Webshop::getWebshopById($params['webshop_id']);
        $params['content'] = parse('settings/settings_webshop_design',$params);        
        return $params;
    }
}