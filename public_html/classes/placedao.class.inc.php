<?php
class PlaceDao{
    
     public static function getDelivery($deliveryId,$absQuantity=false){
        $sql = sprintf('
                SELECT
                    s.id,
                    s.id stock_id,
                    c.id article_id,  
                    d.id delivery_id,
                    s.product_id,
                    s.configuration_id,
                    ABS(SUM(s.quantity)) quantity,
                    c.article_number,
                    c.article_name,
                    wl.name
                FROM
                    delivery d,
                    stock s,
                    catalogue c,
                    transfer t,
                    warehouse_locations wl
                WHERE 
                    d.id=%d
                AND t.did=d.id
                AND wl.id=location_id
                AND d.id=s.delivery_id
                AND c.id=s.product_id
                GROUP BY c.id                                
                ',$deliveryId);
        $result = fetchArray($sql,__METHOD__);
        
        if($absQuantity && is_array($result))
            foreach($result as $row=>$val)
                $result[$row]['quantity'] = abs($val['quantity']); 
                            
        if($result[0]['id'])
            return $result;     
     }
 
}