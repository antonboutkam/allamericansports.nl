<?php
class Settings_analytics_code{
    function  run($params){
        
        
        $params['shopname'] = Webshop::getWebshopById($params['webshop_id']);
		
        if($params['_do']=='set_analytics_code'){
			
            Webshop::setWebshopSetting($params['webshop_id'],'google_analytics', $params['analytics_cd']);                  
        }    
		$params['analytics_cd'] = Webshop::getWebshopSetting($params['shopname'],'google_analytics');        
        $params['content']              = parse('settings_analytics_code',$params);
        return $params;
    }
}