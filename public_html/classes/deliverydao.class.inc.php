<?php
class DeliveryDao{
     /**
      * DeliveryDao::createBlank()
      * 
      * @return a delivery id
      */
     public static function createBlank($type='external',$completed='0'){
        $sql    = sprintf('INSERT INTO delivery (user_id,type,completed) VALUE (%d,"%s",%d)',User::getId(),$type,$completed);
        return query($sql,__METHOD__);
     }
     public static function getTranslatedDeliveryTime($deliverTimeId,$langCode){
        $cacheKey = 'DeliveryDao_getTranslatedDeliveryTime'.$deliverTimeId.$langCode;
        $out = GlobalCache::isCached($cacheKey);
            if(!$out){
            $langCode = ($langCode=='gb')?'en':quote($langCode);
            $sql = sprintf('
                        SELECT 
                            dtt.label 
                        FROM
                            locales l,
                            delivery_time_translation dtt
                        WHERE
                            l.id=dtt.fk_locale
                        AND dtt.fk_delivery_time=%d
                        AND l.locale="%s"',$deliverTimeId,$langCode);
            
            $out = fetchVal($sql,__METHOD__);
            #exit($out);
            GlobalCache::store($cacheKey,$out);
            
        }
        return $out;                          
     }
     public static function updateLocation($locationId,$stockId){
        $sql = sprintf('UPDATE stock SET configuration_id=%d WHERE id=%d',$locationId,$stockId);
        query($sql,__METHOD__);
     }
     public static function updateQuantity($quantity,$stockId){
        query($sql = sprintf('UPDATE stock SET quantity=%d WHERE id=%d',$quantity,$stockId),__METHOD__);
     }     
     public static function remove($stockId){
        query($sql = sprintf('DELETE FROM stock WHERE id=%d',$stockId),__METHOD__);
     }
     public static function completable($id){
        $result = fetchVal($sql = sprintf('SELECT COUNT(*) inc 
                                            FROM delivery d, stock s 
                                            WHERE s.delivery_id=d.id 
                                            AND configuration_id IS NULL
                                            AND d.id=%d',$id),__METHOD__);                                                    
        if($result)
            return "not_all_complet";
     }
     public static function complete($id){
        query($sql = sprintf('UPDATE delivery SET completed=1 WHERE id=%d',$id),__METHOD__);
     }
     public static function insertDeliveryRecord($deliveryId,$productId,$configurationId,$quantity){
        $sql = sprintf('INSERT INTO 
                            stock ( delivery_id,
                                    product_id,
                                    configuration_id,
                                    quantity)
                            VALUE (%d,%d,%s,%d)',
                            $deliveryId,
                            $productId,
                            ($configurationId)?$configurationId:'NULL',
                            $quantity);
        return  query($sql,__METHOD__);         
     }
     public static function addProductToStock($deliveryId,$productId){
        $stockId = self::productInCurrentDelivery($deliveryId,$productId);
        
        if($stockId)
            self::increaseStockQuantity($stockId);
        else{
            // Check to see if we already have this product type in our warehouse            
            $warehouseLocations = WarehouseDao::getWarehouseProductLocations($productId,User::getLocaton(),'wc.id');                        
            return self::insertDeliveryRecord($deliveryId,$productId,$warehouseLocations[0]['id'],1);                               
        }                                                                
     }
     function removeDelivery($id){
        query($sql = sprintf('DELETE FROM stock WHERE delivery_id=%d',$id),__METHOD__);
        query($sql = sprintf('DELETE FROM delivery WHERE id=%d',$id),__METHOD__); 
     }
     public static function getUserUnfinishedDeliveryId($userId){
        return fetchVal($sql = sprintf("SELECT id
                                        FROM delivery
                                        LEFT JOIN transfer ON delivery.id=transfer.did
                                        WHERE user_id=%d
                                        AND transfer.tid IS NULL
                                        AND completed=0",$userId),__METHOD__);
     }          
     public static function getOrderDeliveryId($orderid){
        return fetchVal($sql = sprintf('SELECT 
                            s.delivery_id 
                        FROM 
                            order_item oi,
                            stock s
                        WHERE 
                            s.order_item_id=oi.id                            
                        AND oi.order_id=%d
                        LIMIT 1',$orderid),__METHOD__);                                                    
     } 
     public static function getDeliveryInfo($deliveryId){
        $sql = sprintf('SELECT * FROM delivery WHERE id=%d',$deliveryId);
        echo $sql; 
        return fetchRow($sql,__METHOD__);
     }
     
     public static function getDeliveryDetail($deliveryId){
        
         echo "OrderDao::getDeliveryDetail() IS DEPRICATED";
         /*
        $sql = sprintf('
                SELECT 
                    d.id,
                    d.type,
                    DATE_FORMAT(d.current_time,"%%d/%%m/%%Y %%H:%%i") print_date,
                    d.user_id,
                    u.full_name,
                    IF(d.completed= 1,"yes","no") completed 
                FROM 
                    delivery d,
                    users u
                WHERE 
                    u.id=d.user_id
                AND d.id=%d',$deliveryId);
        
        return fetchRow($sql,__METHOD__);
          *
          */
     }     
     public static function getDeliveryId($orderId){
        $sql = sprintf('SELECT 
                        d.id 
                        FROM 
                        delivery d,
                        stock s,
                        order_item oi
                        WHERE
                        oi.id = s.order_item_id
                        AND s.delivery_id = d.id
                        AND oi.order_id = %d
                        GROUP BY d.id',$orderId);
        #echo nl2br($sql);                        
        return fetchVal($sql,__METHOD__);
     }
     public static function getDelivery($deliveryId,$absQuantity=false){
        $sql = sprintf('
                SELECT
                    s.id,
                    c.id article_id,  
                    s.delivery_id,
                    s.product_id,
                    s.configuration_id,
                    s.quantity,
                    c.article_number,
                    c.article_name,
                    wc.location_id,
                    wc.path,
                    wc.rack,
                    wc.shelf,
                    wl.name,
                    oi.package_box
                FROM
                    delivery d
                    LEFT JOIN stock s ON d.id=s.delivery_id
                    LEFT JOIN catalogue c ON c.id=s.product_id
                    LEFT JOIN warehouse_configuration wc ON wc.id=s.configuration_id
                    LEFT JOIN warehouse_locations wl ON wl.id=wc.location_id,
                    stock sx
                    LEFT JOIN order_item oi ON oi.id=sx.order_item_id 	 
                WHERE 
                    d.id=%d
                AND s.id=sx.id                   
                
                ',$deliveryId);
        #echo nl2br($sql);
        $result = fetchArray($sql,__METHOD__);
        
        if($absQuantity && is_array($result))
            foreach($result as $row=>$val)
                $result[$row]['quantity'] = abs($val['quantity']); 
                            
        if($result[0]['id'])
            return $result;     
     }
     public static function productInCurrentDelivery($deliveryId,$productId){
        return fetchVal($sql = sprintf('
                        SELECT 
                            s.id 
                        FROM 
                            stock s,
                            delivery d 
                        WHERE 
                            s.product_id=%d 
                        AND d.completed=0 
                        AND s.delivery_id=%d
                        AND d.id=s.delivery_id',
                        $productId,$deliveryId),__METHOD__);        
     }
     private static function increaseStockQuantity($stockId){
        query($sql = sprintf('UPDATE stock s 
                                SET s.quantity=s.quantity+1 
                                WHERE s.id=%d',$stockId),__METHOD__);
     }
}