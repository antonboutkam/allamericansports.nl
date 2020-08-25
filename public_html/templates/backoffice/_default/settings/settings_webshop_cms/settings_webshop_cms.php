<?php
class Settings_webshop_cms{
    function  run($params){                               
        $params['webshops'] =  Webshop::getAvailable();
        
        if(!isset($params['webshop_id']))
            $params['webshop_id'] = $params['webshops'][0]['id'];
                                        
        $params['shopname']     = Webshop::getWebshopById($params['webshop_id']);        		
        $params['languages']    = TranslateWebshop::getWebshopLocales($params['webshop_id']);
        
        
        if(!isset($params['fk_locale']) && !isset($_SESSION['settings_webshop_cms_locale'])){
            $params['fk_locale'] = TranslateWebshop::getDefaultLocale($params['webshop_id']);            
        }else if(!isset($params['fk_locale'])){            
            $params['fk_locale'] = $_SESSION['settings_webshop_cms_locale'];            
        }else if(isset($params['fk_locale'])){            
            $_SESSION['settings_webshop_cms_locale'] = $params['fk_locale'];
        }        

        if($params['_do']=='delete')
            Webshopcms::delete($params['webshop_id'],$params['id']);
                
        
        if(!isset($params['query']) && isset($_SESSION['settings_webshop_cms_query'])){
            $params['query'] = $_SESSION['settings_webshop_cms_query'];
        }else if(isset($params['query'])){
            $_SESSION['settings_webshop_cms_query'] =$params['query'];
        }
        
        
        
        $params['current_page']         = (isset($params['current_page']))?$params['current_page']:1;        
        $params['pages']                = Webshopcms::getPages($params['webshop_id'],$params['current_page'],20,$params['query'],$params['fk_locale']);             
        $params['webshop_cms_pages']    = parse('webshop_cms_pages',$params,__FILE__);
        $params['content']              = parse('settings_webshop_cms',$params,__FILE__); 
        return $params;
    }
 
}