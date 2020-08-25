<?php
class Settings_webshop_products{
    function  run($params){
        $params['product_types']    =   ProductTypeDao::getAll();
        $params['shopname']         =   Webshop::getWebshopById($params['webshop_id']);

        if($params['type_visibility'])
            foreach($params['type_visibility'] as $type=>$visibility)
                ProductTypeDao::setWebshopVisibility($params['webshop_id'],$type,$visibility);


        $params['type_visibility']  =   ProductTypeDao::getWebshopProductTypes($params['shopname']);

        foreach($params['product_types'] as $id=> $type)
             if($params['type_visibility'][$type['id']]['visibility_set_by']=='_default'&&($params['shopname']!='_default'))
                 $params['product_types'][$id]['visibility'] = '_default';
             else
                 $params['product_types'][$id]['visibility'] = $params['type_visibility'][$type['id']]['visible'];

        // Happens only at first install, takes care that _default will have everything visible
        if((!is_array($params['type_visibility'])||count($params['type_visibility'])==0)&&$params['shopname']=='_default')
            foreach($params['product_types'] as $type)
                ProductTypeDao::setWebshopVisibility($params['webshop_id'],$type['id'],1);

        $params['content']          =   parse('settings/settings_webshop_products',$params);
        return $params;
    }
}