<?php
class Settings_fielddef{
    function  run($params){        
        if($params['type'])
            ProductTypeDao::add($params['type']);
        if($params['color'])
            ColorDao::add($params['color']);
        if($params['usage']){
            ProductUsageDao::add($params['usage']);
        }    
        $colors        = ColorDao::getAll();
                   
        if(!empty($colors)){
            foreach($colors as $color){
                $tmpColors[] = $color['color']; 
            }
            $params['colors'] = join(',',$tmpColors);
        }
                
        $usages        = ProductUsageDao::getAll();          
        if(!empty($usages)){
            foreach($usages as $usage){
                $tmpUsages[] = $usage['type']; 
            }
            $params['usages'] = join(',',$tmpUsages);
        }        
        $product_types = ProductTypeDao::getAll();


        if(!empty($product_types)){
            foreach($product_types as $product_type){
                $tmpTypes[] = $product_type['type']; 
            }
            $params['product_types'] = join(',',$tmpTypes);
        }


        // $params['webshops'] = gl()
        return $params;
    }
}