<?php
class Settings_webshop_navigationbannerphoto{
    function  run($params){
        if($params['_do'] == 'delete'){
            Webshop::deleteMenuBannerImage($params['menu_id']);
            $tpl = '/settings/webshop_navigationbannerphoto.html?&parent=%d&menu_id=%d&webshop_id=%d';
            $url = sprintf($tpl,$params['parent'],$params['menu_id'],$params['webshop_id']);
            redirect($url);
            exit();
        }

        if(isset($_FILES['image'])){
            if($_FILES['image']['type']!='image/jpeg'){
                $params['errors'][] = 'Voor dit onderdeel kan uitsluitend gebruik gemaakt worden van jpg bestanden..';                
            }else{
                Webshop::storeMenuBannerImage($params['img_for_webshop_id'],$params['img_for_menu_id']);
            }  
        }
        $params['current_image'] = Webshop::getMenuBannerImage($params['webshop_id'],$params['menu_id']);
        
                
        //[img_for_webshop_id] => 3
        //[img_for_menu_id] => 74189
       

        $params['content'] = parse('settings/settings_webshop_navigationbannerphoto',$params);

        return $params;
    }
}
