<?php
class Settings_translations{
    function  run($params){
        
        
        $params['shopname'] = Webshop::getWebshopById($params['webshop_id']);        
        if($params['_do']=='set_translations'){
            $params['translation'][$params['default_language']] = 1;
            TranslateWebshop::setLocales($params['webshop_id'],$params['translation']);            
            TranslateWebshop::setDefaultLocale($params['default_language'],$params['webshop_id']);                        
        }    
        $params['default_language']     = TranslateWebshop::getDefaultLocale($params['webshop_id']);
        $params['locales']              = TranslateWebshop::getAllLocales($params['webshop_id']);
        $params['columns']              = array_columns($params['locales'],3);                
        $params['content']              = parse('settings_translations',$params);
        return $params;
    }
}