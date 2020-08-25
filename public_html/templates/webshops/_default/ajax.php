<?php
class Ajax{
    public static function run($params){      
        if($params['_do']=='set_session'){            
            session_id($params['session']);
            session_start(); 
        }
        
        if($params['_do']=='order_product'){            
            $params['added_product'] = ProductDao::getById($params['id']);            
            Shoppingbasket::addProduct($params['id'], $params['quantity']);                                 
            Shoppingbasket::setDelivery($params['hostname'],$_SESSION['delivery_yn'],$_SESSION['international_order']);        
        }            
                    
        if(in_array($params['_do'],array('set_session','order_product'))){
            $params               = Shoppingbasket::getCart($params);
            $params['cart_small'] = parse('inc/cart_small',$params);                 
        }

        if($params['_do']=='update_cart'){            
            parse_str($params['data'],$data);                  
            if(!empty($data['cart'])){                      
                if($params['module_stockpile']==1){
                    foreach($data['cart'] as $productId=>$quantity){
                        $stock = StockDao::getCurrentTotalStock($productId);                                                         
                        if($stock<$quantity){
                            $data['cart'][$productId]  =  $stock;
                            $params['to_many'] = $stock;
                        }
                    }
                }
                Shoppingbasket::update($data);
                Shoppingbasket::setDelivery($params['hostname'],$_SESSION['delivery_yn'],$_SESSION['international_order']);        
            }
        }
        if($params['_do']=='remove_from_cart'){
            Shoppingbasket::remove($params['id']);
            Shoppingbasket::setDelivery($params['hostname'],$_SESSION['delivery_yn'],$_SESSION['international_order']);        
        }
            

        if($params['_do']=='newsletter')
            if($params['inout']=='in')
                Newsletter::signIn($params['hostname'],$params['email'],$params['name']);
            else if($params['inout']=='out')
                Newsletter::signOut($params['hostname'],$params['email'],$params['name']);
        
        // Juiste vertaling in templates parsen
        Translate::init($params['lang'],'basket');
        $tmpTrans = Translate::getTranslation();  
        $params                     = Shoppingbasket::getCart($params);

        if(!empty($params['basket'])){
            foreach($params['basket'] as &$orderItem){
                $colors     =  ColorDao::getProductColors($orderItem['id']);
                $joinColors = array();
                if(!empty($colors)){
                    foreach($colors as $color){
                        $joinColors[] = $color['color'];
                    }
                }
                $orderItem['colors']        = join(',',$joinColors);
                $orderItem['size_label']    = TranslatedLookup::getTranslatedValue('product_size',Lang::getCodeByLanguageId($lang),$orderItem['fk_size']);                
            }
        } 

        $params['cart_table']       = parse('inc/cart_table',$params);
        $params['cart_items']       = Shoppingbasket::getTotalQuantity();
        $params['cart_items_zf']    = $params['cart_items'];
        #$params['cart_items_zf']    = str_pad(Shoppingbasket::getTotalQuantity(),4,'0',STR_PAD_LEFT).' '.$tmpTrans['trans_products'];
        exit(json_encode($params));
    }
}