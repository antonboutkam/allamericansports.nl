<?php
class OrderDao{
    public static function setValById($field,$value,$id){
        $sql = sprintf('UPDATE orders SET %s=%s WHERE id=%d',$field,$value,$id);            
        query($sql,__METHOD__);        
    }
    public static function storeTrackTrace($orderid,$ttUrl){
        $tpl = 'UPDATE orders SET tt_url="%s" WHERE id=%d';
        $sql = sprintf($tpl,$ttUrl,$orderid);
        query($sql,__METHOD__);
    }
    public static function setProp($orderId,$field,$val){
        $sql = sprintf('UPDATE orders SET %s="%s" WHERE id=%d',$field,quote($val),$orderId);
        query($sql,__METHOD__);
    }
    public static function getBy($field,$val){
        $sql = sprintf('SELECT * FROM orders WHERE %s="%s" LIMIT 1',$field,quote($val));
        #echo $sql;
        return fetchRow($sql,__METHOD__);
    }

    public static function markCancelled($orderId,$relationId,$value=1){
        $sql = sprintf('UPDATE orders SET cancelled=%d WHERE id=%d AND relation_id=%d',$value,$orderId,$relationId);
        query($sql,__METHOD__);
    }
    public static function orderBelongsToRelation($orderId,$relationId){
        $sql = sprintf('
                SELECT o.id FROM 
                    orders o, 
                    relations r 
                WHERE 
                    r.id=o.relation_id 
                AND o.id=%d AND r.id=%d',$orderId,$relationId);
        $out = fetchVal($sql,__METHOD__);
        if($out)
            return true;
        return false;                                     
    }
    public static function setSendCost($orderId,$sendCost){
        if(!$sendCost)
            $sendCost = '0.00';
        query($sql = sprintf('UPDATE orders SET send_cost = %s WHERE id=%d',  quote($sendCost),$orderId),__METHOD__);
    }
    public static function getOrderIdByOrderItemId($orderItemId){
        return fetchVal($sql = sprintf('SELECT product_id FROM order_item WHERE id=%d',$orderItemId),__METHOD__);
    }        
     public static function storeDetails($orderid,$details,$hostname='allamericansports.nl'){         
        $settings['pay_method']         = $details['payment']['method'];
        $row                            = fetchRow(sprintf('SELECT * FROM paymethods WHERE id=%d',$details['payment']['method']),__METHOD__);
        $settings['discount_fixed']     = '0.00';
        $settings['discount_perc']      = '0.00';
        $settings['pay_fee_fixed']      = '0.00';
        $settings['pay_fee_perc']       = '0.00';
                             
        if(!isset($details['fk_locale'])){
            $details['fk_locale'] = Lang::getLocaleIdByLanguageCode('gb');
        }
        
        $settings['fk_locale']          = $details['fk_locale']; 
        
        if($row['price_type']=='fixed_amount')
            $settings['pay_fee_fixed']     = $row['price_amount'];        
        if($row['price_type']=='percentage')
            $settings['pay_fee_perc']      = $row['price_amount'];   
                                        
        if($details['discount']['fixed_on'])
            $settings['discount_fixed'] = $details['discount']['euro'].'.'.$details['discount']['eurocent'];
        if($details['discount']['perc_on'])
            $settings['discount_perc']  = $details['discount']['perc'].'.'.$details['discount']['promille'];

        if($details['delivery']=='direct'){
            $settings['send'] = 'NOW()';
            $settings['send_cost'] = 0;
        }else{
            if(isset($details['send_cost'])){
                $settings['send_cost'] = str_replace(',','.',$details['send_cost']);
            }else if(isset($details['delivery'])){
                $settings['send_cost'] =  ShoppingbasketDb::getSendCost($hostname,$params['orderid'],$details['delivery'],0,false);
            }else
                $settings['send_cost'] =  ShoppingbasketDb::getSendCost($hostname,$params['orderid'],1,0,false);			
            $settings['send'] = 'null';
        }
        if(empty($settings['send_cost'])){
            $settings['send_cost'] = '0';
        }
        $sql = sprintf('UPDATE orders SET fk_locale=%d, pay_method=%d,note="%s", discount_fixed=%s,discount_perc=%s,pay_fee_fixed=%s,pay_fee_perc=%s, send_cost=%f, send=%s WHERE id=%d',
                        $settings['fk_locale'], 
                        $settings['pay_method'], 
                        quote($details['payment']['note']),
                        $settings['discount_fixed'],
                        $settings['discount_perc'],
                        $settings['pay_fee_fixed'],
                        $settings['pay_fee_perc'],
                        $settings['send_cost'],
                        $settings['send'],
                        $orderid);    
        #mail('info@nuicart.nl','query',$sql."Vars:\n".print_r($settings,true));
                                    
        query($sql,__METHOD__);        
     }       
     public static function getUserUnfinishedOrderId($userId){
        return fetchVal($sql = sprintf("SELECT id FROM orders WHERE user_id=%d AND accepted=0",$userId),__METHOD__);
     }  
     public static function getPickCount($locationId=null){
        $conditions[]  =   '((o.accepted IS NOT NULL AND o.paid IS NOT NULL) OR r.buyoncredit=1 OR LOWER(pm.name)="rembours")';
        $conditions[]  =   'o.picked IS NULL';
        $data          =   OrderDao::find($conditions);
        return $data['rowcount'];
        /*
        $extraWhere = '';
        if($locationId)
            $extraWhere = sprintf(' AND location_id=%d',$locationId);            
        $sql = sprintf('SELECT COUNT(*) picked FROM orders WHERE picked_by IS NULL AND accepted IS NOT NULL %s',$extraWhere);       
        return fetchVal($sql,$locationId,__METHOD__);
        */
     }
     public static function updateStock($orderid, $locationId=null){
            // Release the currently reserved stock items first
            query($sql = sprintf("DELETE FROM
                                    stock WHERE
                                    order_item_id IN (SELECT id FROM order_item WHERE order_id=%d)",$orderid),__METHOD__);
            if($locationId==null)
                $locationId = WarehouseDao::getMainWarehouseId();

            $orderItems = self::getOrderItems($orderid);
            $deliveryId = DeliveryDao::createBlank('sale',1);

            foreach($orderItems['data'] as $item){
                $warehouse = fetchArray($sql = sprintf('SELECT
                                                    SUM(s.quantity) quantity, 
                                                    s.configuration_id,
                                                    IF (wc.location_id = %d,0,1) prio
                                                    FROM stock s, warehouse_configuration wc
                                                    WHERE s.product_id=%d 
                                                    AND wc.id = s.configuration_id                                                    	
                                                    GROUP BY s.configuration_id
                                                    ORDER BY prio',
                                                    $locationId,
                                                    $item['product_id']),__METHOD__);

                foreach($warehouse as $location){
                    if($item['quantity']>0 && $location['quantity']>0){
                        if($location['quantity']>=$item['quantity']){   
                            $updateQuantity     = $item['quantity'];
                            $item['quantity']   = 0;                            
                        }else if($location['quantity']<$item['quantity']){
                            $updateQuantity     = $location['quantity'];
                            $item['quantity']   = $item['quantity'] - $location['quantity'];                    
                        }
                        $sql = sprintf('INSERT INTO stock 
                                        (delivery_id,product_id,configuration_id,order_item_id,quantity)
                                        VALUE(%d,%d,%d,%d,-%s)',
                                        $deliveryId,
                                        $item['product_id'],
                                        $location['configuration_id'],
                                        $item['oi_id'],
                                        $updateQuantity);
                        query($sql,__METHOD__);
                    }
                }                                                    
            }  
     }
     public static function setBoxConfig($boxConfig,$orderId){
        if(is_array($boxConfig['box']))
            foreach($boxConfig['box'] as $articleNum=>$box){
                query($sql = sprintf('UPDATE order_item
                                        SET package_box="%1$s"
                                        WHERE order_id=%2$d AND product_id=%3$d',
                                addslashes($box),$orderId,$articleNum),__METHOD__);;
            }
     }
     public static function completePickingAndSending($orderid){
        $sql = sprintf('UPDATE orders 
                        SET picked=NOW(), picked_by=%1$d, send=NOW(), send_by=%1$d
                        WHERE id=%2$d',User::getId(),$orderid);                     
        query($sql,__METHOD__);        
     } 
     public static function directDelivery($orderid,$paymentTookPlace=1){
        $paymentSql = '';
        if($paymentTookPlace){
            $paymentSql = sprintf(', paid=NOW(), payment_approved_by=%1$d',User::getId());
        }
        
        $sql = sprintf('UPDATE orders 
                        SET picked=NOW(), picked_by=%1$d %3$s
                        WHERE id=%2$d',User::getId(),$orderid,$paymentSql);                     
        query($sql,__METHOD__);                        
     }    
     public static function find($where=null,$currentPage=1,$sort=null,$itemsppOverride = false,$includeDeleted=false){        
        $where = (is_array($where))?'AND '.join(PHP_EOL.' AND ',$where):'';            
        
        $itemspp = Cfg::get('items_pp');

        if($itemsppOverride){
            $itemspp = $itemsppOverride;
        }

        if($currentPage){
            $limit          = sprintf('LIMIT %d, %d',$currentPage*$itemspp-$itemspp,$itemspp);
        }

        $sOrderBy = '';
        if($sort){
            $sOrderBy       = 'ORDER BY '.$sort;
        }
        if(!$includeDeleted)
            $extraWhere = 'AND o.is_deleted = 0';
        
            
            
        $btw = Cfg::getPref('btw');
        $sql = sprintf('SELECT
                    SQL_CALC_FOUND_ROWS 
                    o.*,
					DATE_FORMAT(o.exact_salesregisterd,"%%d %%b %%k:%%i") exact_salesregisterd_short,
					DATE_FORMAT(o.montapacking_sent,"%%d %%b %%k:%%i") montapacking_sent_short,					
                    DATE_FORMAT(o.order_date, "%%Y-%%m-%%d") order_date_vis,
                    DATE_FORMAT(o.accepted, "%%Y-%%m-%%d") accepted_date,
                    DATE_FORMAT(o.paid, "%%Y-%%m-%%d") paid_date,             
                    ua.full_name accepted_user,
                    us.full_name send_user,
                    up.full_name picked_user,
                    upab.full_name payment_approved_user,
                    IF(o.send_cost > 0,1,0) send_order,
                    pm.name paymethod,
                    r.company_name,
					r.billing_street, 
					r.billing_city,
					r.billing_postal,
					
                    IF(r.company_name = "",CONCAT(r.cp_firstname," ",r.cp_lastname),r.company_name) as company_or_person,
                    IF(r.company_name = "","P","C" ) as client_type,
                    r.buyoncredit,
                    r.id relation_id,
                    ws.hostname shopname,
                    pm.price_type,
                    CASE 
                        WHEN o.paid IS NOT NULL THEN "paid"
                        WHEN o.send IS NOT NULL THEN "send"
                        WHEN o.picked IS NOT NULL THEN "picked"
                        WHEN o.accepted IS NOT NULL THEN "accepted"
                        WHEN o.cancelled = 1 THEN "cancelled"                                                 
                        ELSE "parked" END as status,
                    CASE 
                        WHEN o.paid IS NOT NULL THEN DATE_FORMAT(o.paid,"%%a %%d %%b %%Y %%T")
                        WHEN o.send IS NOT NULL THEN DATE_FORMAT(o.send,"%%a %%d %%b %%Y %%T")
                        WHEN o.picked IS NOT NULL THEN DATE_FORMAT(o.picked,"%%a %%d %%b %%Y %%T")
                        WHEN o.accepted IS NOT NULL THEN DATE_FORMAT(o.accepted,"%%a %%d %%b %%Y %%T")                        
                        ELSE DATE_FORMAT(o.order_date,"%%a %%d %%b %%Y %%T") END last_status,
                    CASE 
                        WHEN o.paid IS NOT NULL THEN DATE_FORMAT(o.paid,"%%m-%%d %%H:%%i")
                        WHEN o.send IS NOT NULL THEN DATE_FORMAT(o.send,"%%m-%%d %%H:%%i")
                        WHEN o.picked IS NOT NULL THEN DATE_FORMAT(o.picked,"%%m-%%d %%H:%%i")
                        WHEN o.accepted IS NOT NULL THEN DATE_FORMAT(o.accepted,"%%m-%%d %%H:%%i")                        
                        ELSE DATE_FORMAT(o.order_date,"%%m-%%d %%H:%%i") END last_status_short,                        
                    CASE
                        WHEN pm.price_type = "percentage" THEN ROUND(((SUM(oi.sale_price*oi.quantity)+o.send_cost)/100 * (o.pay_fee_perc+100)),2)
                        WHEN pm.price_type = "fixed_amount" THEN (SUM(oi.sale_price*oi.quantity)+o.send_cost) + o.pay_fee_fixed
                    ELSE SUM(oi.sale_price)+o.send_cost
                    END sub_total,
                    CASE
                        WHEN pm.price_type = "percentage" THEN ROUND(((SUM(oi.sale_price*oi.quantity)+o.send_cost)/100 * (o.pay_fee_perc+100)) * 0.%4$s,2)
                        WHEN pm.price_type = "fixed_amount" THEN ((SUM(oi.sale_price)+o.send_cost*oi.quantity) + o.pay_fee_fixed) * 0.%4$s
                        ELSE SUM(oi.sale_price)+o.send_cost * 0.%4$s
                    END vat,
                    CASE
                        WHEN pm.price_type = "percentage" THEN ROUND(((SUM(oi.sale_price*oi.quantity)+o.send_cost)/100 * (o.pay_fee_perc+100)) * 1.%4$s,2)
                        WHEN pm.price_type = "fixed_amount" THEN ((SUM(oi.sale_price*oi.quantity)+o.send_cost) + o.pay_fee_fixed) * 1.%4$s
                        ELSE (SUM(oi.sale_price*oi.quantity)+o.send_cost) * 1.%4$s
                    END total
                FROM
                    -- relations r,
                    order_item oi,
                    orders o                    
                    LEFT JOIN users ua ON o.accepted_by = ua.id
                    LEFT JOIN users up ON o.picked_by = up.id
                    LEFT JOIN users us ON o.send_by = us.id
                    LEFT JOIN users upab ON o.payment_approved_by = upab.id
                    LEFT JOIN relations r ON r.id=o.relation_id
                    LEFT JOIN webshops ws ON ws.id=o.fk_webshop,
                    orders ojoin
                    LEFT JOIN paymethods pm ON pm.id=ojoin.pay_method  
                WHERE
                    1=1
                %1$s
                AND oi.order_id = o.id                    
                AND ojoin.id=o.id
                -- AND o.is_deleted = 0
                %5$s
                -- AND r.id=o.relation_id
                GROUP BY o.id
                %2$s
                %3$s',
                $where,
                $sOrderBy,
                $limit,
                $btw,
                $extraWhere);
        
        $data['data']     = fetchArray($sql,__METHOD__);
        $data['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        
        if(is_array($data['data']))
            foreach($data['data']  as $id=>$row){                
                if(!$row['paid']){
                    $data['data'][$id]['exact_salesorder_vis'] = 'not paid';                 
                }else if(!$row['exact_salesorder']){
                    $data['data'][$id]['exact_salesorder_vis'] = 'waiting';                 
                }else{
                    $data['data'][$id]['exact_salesorder_vis'] = $row['exact_salesorder'];                 
                }
                
            
                if(strlen($row['company_or_person'])>10){
                    $data['data'][$id]['company_or_person_short'] = substr(trim(stripslashes($row['company_or_person'])),0,7).'...';
                }else{
                    $data['data'][$id]['company_or_person_short'] = stripslashes($row['company_or_person']);
                }
                if(strlen($row['shopname'])>13){
                    $data['data'][$id]['shopname_short'] = substr(trim(stripslashes($row['shopname'])),0,10).'...';
                }else{
                    $data['data'][$id]['shopname_short'] = stripslashes($row['shopname']);
                }				
				
                $data['data'][$id]['ordernumber']               =   str_pad($row['id'], 6, "0", STR_PAD_LEFT);
            }                     
        else
            return;

        return $data;
     } 
     /**
      * DeliveryDao::createBlank()
      * 
      * @return a delivery id
      */
     public static function createBlank($webshop_hostname='_default'){
        $userId         = (User::getId())?User::getId():'null';
        $locationId     = (User::getLocaton())?User::getLocaton():WarehouseDao::getMainWarehouseId();

        $sql    = sprintf('INSERT INTO orders 
                            (user_id,location_id,order_date,fk_webshop) 
                            VALUE (%s,%d,NOW(),(SELECT id FROM webshops WHERE hostname="%s"))',
                            $userId,$locationId,$webshop_hostname);        
        
        return query($sql,__METHOD__);
     }
     public static function addClient($orderId,$clientId){
        $sql    = sprintf('UPDATE orders SET relation_id=%d WHERE id=%d',
                            $clientId,$orderId);
        return query($sql,__METHOD__);
     }
     public static function removeOrderItem($orderId,$orderItemId){
        $sql = sprintf('DELETE FROM stock WHERE order_item_id=%d',$orderItemId);
        query($sql,__METHOD__);        
        $sql = sprintf('DELETE FROM order_item WHERE order_id=%d AND id=%d',$orderId,$orderItemId);                    
        Db::instance()->query($sql,__METHOD__);
     }
     public static function removeOrder($orderId){
        $_SESSION['orders']['orderid']  = null;
        query($sql = sprintf('DELETE FROM stock WHERE order_item_id IN(SELECT id FROM order_item WHERE order_id=%d)',$orderId),__METHOD__);                             
        query($sql = sprintf('UPDATE orders SET is_deleted=1 WHERE id=%d',$orderId),__METHOD__);
     }

     public static function acceptOrder($orderid, $paymenttookplace=0){
        $sql = sprintf('UPDATE orders SET accepted=NOW(), accepted_by=%d WHERE id=%d',User::getId(),$orderid);
        query($sql,__METHOD__);
        $sql = sprintf('UPDATE relations SET type="customer" WHERE id = (SELECT relation_id FROM orders WHERE id=%d)',$orderid);
        query($sql,__METHOD__);
        if($paymenttookplace)
            self::markPaid(User::getId(),$orderid);             
     }
     public static function markPaid($userId,$orderid){        
        if($userId==null)
            $userId = User::getId();
        
        if($userId)                    
            $sql = sprintf('UPDATE orders SET paid=NOW(), accepted=NOW(), payment_approved_by=%d WHERE id=%d',$userId,$orderid);                           
        else
            $sql = sprintf('UPDATE orders SET paid=NOW(), accepted=NOW() WHERE id=%d',$orderid);			
        Log::message('markpaid', $sql, __METHOD__);
        query($sql,__METHOD__);         
     }
     /*
      * Automatically detects the prices and update's those as well!
      */
     public static function setPaymethodProperties($paymethodId,$orderId){
        $paymethod = Paymethod::getById($paymethodId);
		
        //by default transaction is for, free is also a price_type
        $pay_fee_fixed = 0.00;
        $pay_fee_perc = 0.00;
        if($paymethod['price_type']=='fixed'){
             $pay_fee_fixed = (float)	$paymethod['price_amount'];             
         }else if($paymethod['price_type']=='percentage'){
             $pay_fee_perc = $paymethod['price_amount'];             
         }
        $sql = sprintf('UPDATE orders SET pay_method=%d,pay_fee_perc=%s,pay_fee_fixed=%s WHERE id=%d',
                        $paymethodId,$pay_fee_perc,$pay_fee_fixed,$orderId);
        
		query($sql,__METHOD__);
     }
             
     public static function addProduct($orderId,$productId,$quantity=1){                
        $sql = sprintf('INSERT INTO order_item (order_id,product_id,quantity,purchase_price,sale_price,pay_price,discount_perc)
                        VALUES (
                                    %1$d,
                                    %2$d,
                                    %3$d,
                                    (SELECT purchase_price FROM catalogue WHERE id=%2$d),
                                    (SELECT sale_price FROM catalogue WHERE id=%2$d),
                                    ((SELECT sale_price FROM catalogue WHERE id=%2$d)/100)*(100-(SELECT discount FROM catalogue WHERE id=%2$d)),
                                    (SELECT discount FROM catalogue WHERE id=%2$d))
                        ON DUPLICATE KEY UPDATE quantity=quantity+%3$d',
                        $orderId,$productId,$quantity);         
                                               
        return query($sql,__METHOD__);                       
     }
     public static function makeOrderIdVis($order_id){
        return str_pad($order_id, 6, "0", STR_PAD_LEFT);  
     }
     public static function isRelationOrder($orderId,$relationId){
        $sql    = sprintf('SELECT id FROM orders WHERE relation_id=%d AND id=%d',$relationId,$orderId);
        
        $id     = fetchVal($sql,__METHOD__);        
        if($id)
            return true;
     }
     public static function getOrder($orderId){
        $sql = sprintf('SELECT 
                        o.id order_id,
                        o.*,
                        DATE_FORMAT(o.order_date,"%%Y-%%m-%%d") order_date_format,
                        w.hostname,
                        IF(r.company_name = "",CONCAT(r.cp_firstname," ",r.cp_lastname),r.company_name) as company_or_person,
                        IF(r.company_name = "","P","C" ) as client_type,                                    
                        DATE_FORMAT(o.order_date,"%%d/%%c/%%Y") order_date_visible,
                        DATE_FORMAT(ADDDATE(o.order_date,INTERVAL 10 DAY),"%%d/%%c/%%Y") paydate_visible,
                        o.id orderid,
                        (SELECT pm.name FROM paymethods pm WHERE o.pay_method = pm.id) paymethod_visible,
                        r.*,
                        (SELECT full_name FROM users WHERE id=o.user_id) created_by,
                        (SELECT full_name FROM users WHERE id=o.accepted_by) accepted_by,
                        (SELECT full_name FROM users WHERE id=o.picked_by) picked_by,
                        (SELECT full_name FROM users WHERE id=o.send_by) send_by,
                        (SELECT full_name FROM users WHERE id=o.payment_approved_by) payment_approved_by,
                        o.fk_locale,
                        l.locale language_code,
                        l.description language_desc,
                        r.exact_id relation_exact_id                                                                                                            
                    FROM 
                        orders o
                        LEFT JOIN relations r ON r.id=o.relation_id   
                        LEFT JOIN webshops w ON w.id=r.webshop,
                        orders otwo
                        LEFT JOIN locales l ON l.id = otwo.fk_locale                                       
                    WHERE o.id=%d
                    AND otwo.id = o.id',$orderId);        
        #echo nl2br($sql);                    
        $result = fetchRow($sql,__METHOD__);    
        
        
        $result['order_id_vis']     =   self::makeOrderIdVis($result['order_id']);str_pad($result['order_id'], 6, "0", STR_PAD_LEFT);

        list($discperceuro,$discpercent) = explode(".",$result['discount_perc']);
        list($discfixedceuro,$discfixedcent) = explode(".",$result['discount_fixed']);
        $result['discount_perc_cent']   = $discpercent;
        $result['discount_perc_euro']   = $discperceuro;
        $result['discount_fixed_cent']  = $discfixedcent;
        $result['discount_fixed_euro']  = $discfixedceuro;
        
        $result['delivery_pickup']      =   ($result['send_cost']>0)?'delivery':'pickup';
        
        if(trim($result['shipping_street']) && $result['shipping_street']!=$result['billing_street']){
            $result['has_delivery_address'] = true;
        }
        if(trim($result['shipping_number']) && $result['shipping_number']!=$result['shipping_number']){
            $result['has_delivery_address'] = true;
        }        
        return $result;                                                                
     }
     public static function getOrderItems($orderId){
        $result['data'] = fetchArray($sql = sprintf('SELECT 
                                                SQL_CALC_FOUND_ROWS 
                                                c.*,
                                                c.id article_id,
                                                oi.*,
                                                (c.sale_price * oi.quantity) price,
                                                oi.id oi_id,
                                                c.id p_id,
                                                pz.type as vis_size,
                                                IF(s.id IS NULL,0,1) stock_reserved,
                                                oi.quantity
                                            FROM                                                                                                  
                                                catalogue c,
                                                order_item oi
                                                LEFT JOIN stock s ON s.order_item_id = oi.id,
                                                catalogue c2
                                                LEFT JOIN product_size pz ON c2.fk_size = pz.id,
                                                catalogue c3
                                                
                                            WHERE 
                                                c.id=oi.product_id
                                            AND c.id = c2.id
                                            AND c.id = c3.id  
                                            AND oi.order_id=%d',$orderId),__METHOD__);
                                           
        $result['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        
        $btw = '1.'.Cfg::getPref('btw');
        $btw = (float)$btw;
        if(!empty($result['data'])){
            foreach($result['data'] as $id=> $row){
                $result['data'][$id]['price_inc_vat'] = number_format($row['price']*$btw,2,',','.');
                $result['data'][$id]['sale_price_vis_vat'] =  number_format($row['sale_price']*$btw,2,',','.');
            }
        }
        return $result;                                            
     }
     public static function setOrderUser($orderId,$userId,$field){
        $sql = sprintf('UPDATE orders SET %1$s=NOW(), %1$s_by="%2$s" WHERE id=%3$d',$field,$userId,$orderId);
        query($sql,__METHOD__);
     }
     public static function changeQuantity($orderItemId,$new_quantity){
        $sql = sprintf('UPDATE order_item SET quantity=%d WHERE id=%d',$new_quantity,$orderItemId);        
        return query($sql, __METHOD__);
     } 
     
}
