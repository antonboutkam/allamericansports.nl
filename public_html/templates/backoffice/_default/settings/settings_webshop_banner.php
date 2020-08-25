<?php
class Settings_webshop_banner{
    function  run($params){        
        	
            if($params['_do']=='change_order')    
                WebshopBanner::changeOrder($params['webshop_id'],$params['new_order']);
            
            if($params['_do']=='delete')       
                WebshopBanner::delete($params['webshop_id'],$params['id']);                    
            
            if($params['_do']=='store'){                
                $params['id'] = WebshopBanner::store($params['banner']);    
                $params['banner'] = array();                            
            }
            if($params['id']){
                $params['banner'] = WebshopBanner::getItemById($params['id']);                
            }
            $params['banner_items'] = WebshopBanner::getAll($params['webshop_id']);                        
            $params['webshop']  =   Webshop::getWebshopById($params['webshop_id']);
                                    
            /*
            parse_str($params['data'], $save);
            Webshop::storeMenuItem($save['webshop_id'],$save['parent'],$save['menu_item'],$save['menu_id']);            
            */                
        $params['content'] = parse('settings/settings_webshop_banner',$params);
        return $params;
    }
}