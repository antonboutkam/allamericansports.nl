<?php
class Basket{
    function run($params){                                             
        if(ShoppingBasket::getTotalQuantity()==0)
            redirect($params['root'].'/'.$params['lang'].'/checkout.html');
                    
        $params  = self::addMeta($params);
        $params  = Webshop::doFirst($params);
        
        if(!isset($_SESSION['delivery_set']))
            Shoppingbasket::setDelivery($params['hostname'],1,$_SESSION['international_order']);        
        else
            Shoppingbasket::setDelivery($params['hostname'],$_SESSION['delivery_yn'],$_SESSION['international_order']);        
                    
        $params                         = ShoppingBasket::getCart($params);
                                
        if(!empty($params['basket'])){
            foreach($params['basket'] as &$orderItem){
                $colors     =  ColorDao::getProductColors($orderItem['id']);
                $joinColors = array();
                if(!empty($colors))
                    foreach($colors as $color)
                        $joinColors[] = $color['color'];
                                    
                $orderItem['colors']        = join(',',$joinColors);
                $orderItem['size_label']    = TranslatedLookup::getTranslatedValue('product_size',Lang::getCodeByLanguageId($params['lang']),$orderItem['fk_size']);                
            }
        }              		        
        $params['continue_shopping']    = $_SERVER['HTTP_REFERER'];                
        $params['cart_table']           = parse('inc/cart_table',$params);                                        
        $params['content']              = parse($params['root'].'/basket',$params); #pre_r($params);       
        return $params;
    }    
    private static function addMeta($params){
        $params['title_override']       = sprintf('Uw Winkelmandje');
        $params['description']          = sprintf('Winkelwagentje');
        $params['keywords']             = sprintf('Winkelmand, Winkelmandje, Winkelwagen');
        return $params;
    }
    
}