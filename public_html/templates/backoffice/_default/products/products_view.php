<?php
class Products_view{
    function  run($params){            	
        $modules = array('module_stockpile');
        $params['modules'] = Cfg::areModulesActive($modules);                                                
                
        if($params['idtype']=='barcode')
            $params['id'] = BarcodeDao::getProductIdByBarcode($params['id']);
                       
        $params                     = BarcodeDao::getProductProps($params,$params['id']);
        $defaults                   = ProductDao::getDefaults();
        $product                    = ProductDao::getById($params['id']);
                
        $params['stock']            = StockDao::getProductStock($params['id']);

        $params['mode']             = 'view';
        $params['options']          = ProductParts::getAllPartsOf($params['id']);
        $params['partsoftable']     = parse('inc/partsoftable',$params);
        
        $params['options']          = ProductOptions::getAllOptionsOf($params['id']);
        $params['optionoftable']    = parse('inc/partsoftable',$params);

        unset($params['mode']);

        foreach($defaults as $field=>$value)
            if($product[$field]=='')
                $product[$field] = $value;
                
        if(is_array($product))
            $params = array_merge($params,$product);                   

        $params['multi_img'] = Cfg::isModuleActive('multi_img');
        if($params['multi_img']){
            $params['extra_images'] = Image::getExtraImages($params['id']);            
        }

        return $params;
    }
}