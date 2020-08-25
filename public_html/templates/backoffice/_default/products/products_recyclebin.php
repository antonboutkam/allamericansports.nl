<?php
class Products_recyclebin{
    function  run($params){        
        if($params['_do']=='delete_items'){
            foreach($params['items'] as $product_id){
                Image::removeAllProductImages($product_id);
            }
        }
        if($params['_do']=='revert_items'){
            $sql = sprintf("UPDATE catalogue SET deleted =NULL, deleted_by=NULL WHERE id IN(%s)",join(',',$params['items']));                  
            query($sql,__METHOD__);                        
        }        
                
        $itemsPP                                        = 24;
        $params['current_page']                         = (isset($params['current_page']))?$params['current_page']:1;                        
        $img1                                           = Hosting::getDeletedProductPhotosAndSizes('primary_photos');
        $img2                                           = Hosting::getDeletedProductPhotosAndSizes('other_photos');
        $products                                       = array_merge($img1,$img2);        
        $params['paginate']                             = paginate($params['current_page'], count($products), $itemsPP);
        $offset                                         = ($params['current_page']*$itemsPP)-$itemsPP;
        $params['products']                             = array_slice ($products,$offset,$itemsPP);
        $params['products_tbl_recyclebin']              = parse('inc/products_tbl_recyclebin',$params);
                            	                	        
        return $params;
    }
}