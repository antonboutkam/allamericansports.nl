<?php
class Settings_webshop_brandbox{
    function  run($params){        
        $params['brandbox']['fk_webshop']   = $params['webshop_id'];
        

        if(!isset($params['brandbox']['fk_locale'])){            
            $params['brandbox']['fk_locale'] = TranslateWebshop::getDefaultLocale($params['webshop_id']);            
        }        
        if($params['_do'] == 'change_language'){
            unset($params['id']);
        }
        if($params['_do'] == 'store'){            
            $params['id'] = Brandboxdao::store($params['id'],$params['brandbox']);
            #exit($params['request_uri'].'&brandbox[fk_locale]='.$params['brandbox']['fk_locale']);
            redirect($params['request_uri'].'?webshop_id='.$params['webshop_id'].'&brandbox[fk_locale]='.$params['brandbox']['fk_locale']);
        }
        if($params['_do'] == 'delete'){
            Brandboxdao::deleteById($params['id']);
            redirect($params['request_uri'].'?webshop_id='.$params['webshop_id']);
        }
        if(is_numeric($params['id']))
            $params['brandbox']     = Brandboxdao::getById($params['id']);          
                          
                              
        $params['languages']    = TranslateWebshop::getWebshopLocales($params['webshop_id']);                                                            
        $params['brandboxes']   = Brandboxdao::getAll($params['webshop_id'],$params['brandbox']['fk_locale']);
                                                                      
        $params['content']      = parse('settings_webshop_brandbox',$params);        
        return $params;
    }    
    
}