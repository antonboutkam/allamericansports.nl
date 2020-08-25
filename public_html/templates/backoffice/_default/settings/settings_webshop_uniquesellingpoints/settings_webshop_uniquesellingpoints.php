<?php
class Settings_webshop_uniquesellingpoints{
    function  run($params){            
                        
        $params['webshops'] =  Webshop::getAvailable();        
        if(!isset($params['webshop_id']))
            $params['webshop_id'] = $params['webshops'][0]['id'];
        
        $params['shopname']     = Webshop::getWebshopById($params['webshop_id']);
        $params['usp_count']    = 4;//Cfg::isModuleActive('usp_count');
        $params['usp_range']    = range(1,$params['usp_count']);
                                                        				 
        if($params['_do']=='store')
            Weshopusp::store($params['usp']);
                
        $params['usps']         =   Weshopusp::getLanguages($params['usp_count'],$params['webshop_id']);                
        $params['content']      =   parse('settings_webshop_uniquesellingpoints',$params,__FILE__);        
        return $params;
    }
}