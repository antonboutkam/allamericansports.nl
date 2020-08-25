<?php
class Products_catalogue{
    function  run($params){

        $modules = array('webshop_menu_editor','module_stockpile');
        $params['modules'] = Cfg::areModulesActive($modules);
        

        
        #$params['modules']['webshop_menu_editor']   = Cfg::isModuleActive('webshop_menu_editor');
        #'exact_online'
        
        if($params['_do']=='delete_product'){
            Log::message('deleted_products', 'Product '.$params['product_id'].' deleted by '.User::getFirstname(), $method);
            ProductDao::remove($params['product_id']);
        }
        $params['current_page']     = (isset($params['current_page']))?$params['current_page']:1;
        $params['sort']             = ($params['sort'])?$params['sort']:'article_number';
        $params['perspective']      = ($params['perspective'])?$params['perspective']:'list';
        $params['current_location_name'] = User::getLocationName();

        $params['product_types']    = ProductTypeDao::getAll();
        

        $params['rand']             = rand(0,100000);
        if($params['ajaxresult'] && $params['type']!='advanced')
            $params['query']        = ($params['query']==$params['defaultquery'])?'':$params['query'];
            if($params['showwostock'])
                $_SESSION['incWoStock'] = ($params['showwostock']=='true')?true:false; 
             
             if($params['type']=='advanced'){
                parse_str($params['query'],$params['query']);
             }
        $products              = ProductDao::find($params['query'],$params['sort'],null,$params['current_page'],null,$_SESSION['incWoStock']);
        
        if(!empty($products['data'])){
            foreach($products['data'] as $id => $product){
                
                #pre_r($product);
                if(strlen($product['size_name'])>11)
                    $products['data'][$id]['size_name_short'] =  substr($product['size_name'],0,7).'...';
                else
                    $products['data'][$id]['size_name_short'] =  $product['size_name'];
                  
            }
        }
        $params['products']    = $products['data'];
        $params['rowcount']    = $products['rowcount'];
        $params['paginate']    = paginate($params['current_page'],$params['rowcount']);
        
        if($params['perspective']=='thumb'){
            $params['products_tbl']    = parse('inc/products_tbl_thumbs',$params);
        }else{
            $params['products_tbl']    = parse('inc/products_tbl',$params);
        }
        if($params['ajaxresult']){
            print $params['products_tbl'];
            exit(); 
        }
                       
        return $params;
    }
}