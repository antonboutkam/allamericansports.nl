<?php
class Settings_webshop_editnav{
    function run($params){
        // ini_set('display_errors', 1);
        // error_reporting(E_ALL);

        $params['locales'] = TranslateWebshop::getWebshopLocales($params['webshop_id']);        
                                      
        if($params['_do']=='save'){
            $params['menu_id'] =  Webshop::storeMenuItem($params['webshop_id'],$params['parent'],$params['menu_item'],$params['menu_id'],$params['in_mainnav']);                        
            Webshop::storeMenuItemTranslations($params['menu_id'],$params['menu_item_translation'],$params['menu_item_description']);		            
                        
            if(isset($_FILES['file']['error']) && $_FILES['file']['error']=='0'){
                list($ext) = array_reverse(explode(".",$_FILES['file']['name']));
                move_uploaded_file($_FILES['file']['tmp_name'],'./img/leftmenu-img/'.$params['menu_id'].'.'.$ext);
                
                $tpl = 'UPDATE  webshop_menu SET leftmenu_img_ext="%s" WHERE id=%d';
                $sql = sprintf($tpl,$ext,$params['menu_id']);
                query($sql,__METHOD__);                                                                                                
            }        
            $params['menu_item']    = Webshop::getMenuItemById($params['menu_id']);                        
            
        }else if($params['_do']=='add_item'){
            #$params['content']   =   parse('settings_webshop_editnav',$params);
        }else if($params['_do']=='rename_item'){            
            $params['menu_item'] = Webshop::getMenuItemById($params['menu_id']);
        }
       
        #echo "menu_id ".$params['menu_id'];
        if(isset($params['menu_id']) && $params['menu_id'] && !empty($params['locales']) && is_array($params['locales'])){
            foreach($params['locales'] as $itemid => $locale){
                $params['locales'][$itemid]['value'] 		=  Webshop::getMenuItemTranslation($locale['fk_locale'],$params['menu_id']);
                $params['locales'][$itemid]['description_txt'] 	=  Webshop::getMenuItemTranslation($locale['fk_locale'],$params['menu_id'],'description');
            }
        }

        $params['content'] = parse('settings/settings_webshop_editnav',$params);
      //  exit($params['content']);
     //   $params['content'] = parse('inc/add_menu_item',$params);
        return $params;
    }
}