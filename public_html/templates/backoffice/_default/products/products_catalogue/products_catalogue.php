<?php
class Products_catalogue{
    /**
     * Products_catalogue::run()
     * 
     * @param mixed $params
     * @return
     */
    function  run($params)
    {

        if (isset($params['items_pp'])){
            User::setSetting(User::getId(), 'items_pp_products_catalogue', $params['items_pp']);
        }

        $modules = array('webshop_menu_editor', 'module_stockpile');
        $params['modules'] = Cfg::areModulesActive($modules);

        if (isset($params['_do']) && $params['_do'] == 'delete_product') {
            Log::message('deleted_products', 'Product ' . $params['product_id'] . ' deleted by ' . User::getFirstname(), __METHOD__);
            ProductDao::remove($params['product_id']);
        }
        if (!isset($params['current_page']) && isset($_SESSION['products_catalogue']['current_page'])) {
            $params['current_page'] = $_SESSION['products_catalogue']['current_page'];
        }


        $params['sort'] = (isset($params['sort']) && !empty($params['sort'])) ? $params['sort'] : 'id DESC';
        $params['perspective'] = (isset($params['perspective']) && !empty($params['perspective'])) ? $params['perspective'] : 'list';
        $params['current_location_name'] = User::getLocationName();
        $params['product_types'] = ProductTypeDao::getAll();
        $params['rand'] = rand(0, 100000);

        if(!isset($params['type'])){
            $params['type'] = null;
        }
        if (isset($params['ajaxresult']) && $params['type'] != 'advanced') {
            $params['query'] = ($params['query'] == $params['defaultquery']) ? '' : $params['query'];
            if(isset($params['showwostock']) && $params['showwostock']){
                $_SESSION['incWoStock'] = (isset($params['showwostock']) && $params['showwostock'] == 'true') ? true : false;
            }
            $_SESSION['products_catalogue']['last_query'] = $params['query'];
        }

        if (!isset($_SESSION['products_catalogue']['current_page'])) {
            $_SESSION['products_catalogue']['current_page'] = 1;
        }
        $params['current_page'] = (isset($params['current_page'])) ? $params['current_page'] : $_SESSION['products_catalogue']['current_page'];
        $_SESSION['products_catalogue']['current_page'] = $params['current_page'];


        if (isset($_SESSION['products_catalogue']) && isset($_SESSION['products_catalogue']['last_query'])){
            $params['query'] = $_SESSION['products_catalogue']['last_query'];
        }

        if (isset($params['type']) && $params['type'] == 'advanced'){
            parse_str($params['query'], $params['query']);
        }

        $params['items_pp'] = (int)User::getSetting(User::getId(), 'items_pp_products_catalogue');

        if ($params['items_pp'] == 0){
            $params['items_pp'] = 25;
        }

        if(!isset($params['query'])){
            $params['query'] = null;
        }

        if(!isset($_SESSION['incWoStock'])){
            $_SESSION['incWoStock'] = false;
        }
        $products = ProductDao::find($params['query'], $params['sort'], null, $params['current_page'], $params['items_pp'], $_SESSION['incWoStock']);


        if(!empty($products['data'])){
            foreach ($products['data'] as $id => &$product) {

                if (strlen($product['size_name']) > 11) {
                    $products['data'][$id]['size_name_short'] = substr($product['size_name'], 0, 7) . '...';
                } else {
                    $products['data'][$id]['size_name_short'] = $product['size_name'];
                }
                $product['sale_price_vis'] = number_format($product['sale_price'], 2, ",", ".");
                $product['advice_price_vis'] = number_format($product['advice_price'], 2, ",", ".");
            }
        }
        $params['products']    = $products['data'];
        $params['rowcount']    = $products['rowcount'];
        $params['paginate']    = paginate($params['current_page'],$params['rowcount']);
        $params['ledgers']      = Lookup::getItems('ledger');
        
        if($params['perspective']=='thumb'){
            $params['products_tbl']    = parse('products_catalogue/products_tbl_thumbs',$params,__FILE__);
        }else{
            $params['products_tbl']    = parse('products_catalogue/products_tbl',$params,__FILE__);
        }
        if(isset($params['ajaxresult'])){
            print $params['products_tbl'];
            exit(); 
        }
        $params['content'] = parse('products_catalogue/products_catalogue',$params,__FILE__);                  
        return $params;
    }
}
