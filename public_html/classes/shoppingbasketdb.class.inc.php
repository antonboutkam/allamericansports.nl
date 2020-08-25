<?php
class ShoppingbasketDb{
    public static function getBasket($orderId){
        $tmp = OrderDao::getOrderItems($orderId);
        foreach($tmp['data'] as $row=>$field){
            $tmp['data'][$row]['order_quantity'] = $field['quantity'];
            $tmp['data'][$row]['subtotal_vis'] =  number_format($field['sale_price'] * $field['quantity'],2,",",".");;
            $tmp['data'][$row]['sale_price_vis'] = number_format($field['sale_price'],2,",",".");

            $tmp['data'][$row]['sale_price_vis'] = number_format($field['sale_price'],2,",",".");
            $tmp['data'][$row]['sale_price_vis_vat'] = number_format($field['sale_price']*(float)$vat,2,",",".");
        }
        return $tmp['data'];
    }
    public static function setPaymethod($paymethod,$deliveryId){
        OrderDao::setPaymethodProperties($paymethod,$deliveryId);
    }

    public static function getPaymethodValue($deliveryId){
        $order = OrderDao::getOrder($deliveryId);        
        if($order['pay_fee_fixed']==0 && $order['pay_fee_perc']==0)
            return 0;
        else if($order['pay_fee_fixed']>0)
            return $order['pay_fee_fixed'];
        else if($order['pay_fee_perc']>0)
            return $order['pay_fee_perc'];
    }
    public static function getPaymethodCalctype($deliveryId){
        $order = OrderDao::getOrder($deliveryId);     
        
        if($order['pay_fee_fixed']==0 && $order['pay_fee_perc']==0)
            return 'free';
        else if($order['pay_fee_fixed']>0)
            return 'fixed';
        else if($order['pay_fee_perc']>0)
            return 'percentage';
    }
    public static function getPaymethod($deliveryId){
        $order = OrderDao::getOrder($deliveryId);
        return $order['pay_method'];
    }
    public static function getTransactionFee($deliveryId){        
        $calctype    = self::getPaymethodCalctype($deliveryId);              
        if($calctype=='free')
            return '0';
        else{
            $paymethodvalue = self::getPaymethodValue($deliveryId);
            
            $subtotal       = self::getSubtotalUnformat($deliveryId)+self::getDeliveryPrice($deliveryId);
            if($calctype=='percentage'){
                return ($subtotal/100)*$paymethodvalue;
            }else{
                return $paymethodvalue;
            }
        }
    }
    public static function getTransactionFeeVis($deliveryId,$incVat=false){
        $out = self::getTransactionFee($deliveryId);
        if($incVat){
            $vat = '1.'.Cfg::getPref('btw');
            $out = ($out*(float)$vat);             
        }
        return number_format($out,2,",",".");
    }
    public static function getSendCost($hostname,$orderId,$delivery,$internationalOrder,$incVat=true){             

        $shippingCostClass               = Webshop::getWebshopSetting($hostname, 'ship_cost_calc_method');

        $shippingCostClass               = ($shippingCostClass)?$shippingCostClass:'Nocost';
        $shippingCostObject              = new $shippingCostClass;

        $_SESSION['delivery']            = $shippingCostObject->calcShippingCost($hostname,$delivery,$internationalOrder,self::getBasket($orderId));        

        $_SESSION['international_order'] = $internationalOrder;
        $_SESSION['delivery_set']        = true;
        $_SESSION['delivery_yn']         = $delivery;       
        
        $out                             = $_SESSION['delivery'];
        
        if(!$incVat){
            $vat = '1'.Cfg::getPref('btw');
            $out = ($out/$vat)*100; 
        }        
        return $out;       
    }
    public static function setDelivery($hostname,$orderId,$delivery,$internationalOrder){
        $cost = self::getSendCost($hostname,$orderId,$delivery,$internationalOrder);		
        OrderDao::setSendCost($orderId,$cost);

    }
    public static function getDeliveryPrice($orderId,$numberFormatResut=false,$addVat=false){
        $sql = sprintf('SELECT send_cost FROM orders WHERE id=%d',$orderId);        
        $delivery = fetchVal($sql,__METHOD__);
        if($addVat){                
            $vat = '0.'.Cfg::getPref('btw');
            $delivery = $delivery + ($delivery * (float)$vat);
                 
        }        
        if($numberFormatResut)
            $delivery = number_format($delivery,2,",",".");
        return $delivery;
    }
    /**
     * Shoppingbasket::getTotalQuantity()
     * Geeft het totaal aantal producten in de winkelwagen terug. 
     * @return
     */
    public static function getTotalQuantity($orderId){
        $tmp = OrderDao::getOrderItems($orderId);
        if(empty($tmp['data']))
            return 0;
        foreach($tmp['data'] as $row){
            $quantity += $row['quantity'];
        }            
        return $quantity;
    }    
    private static function getSubtotalUnformat($orderId,$incVat =false){
        $tmp = OrderDao::getOrderItems($orderId);
        $subtotal = 0;
        if(is_array($tmp['data']))
            foreach($tmp['data'] as $id=>$row)
                $subtotal = $subtotal+ ($row['sale_price'] * $row['quantity']);

        if($incVat){
            $vat = '1.'.Cfg::getPref('btw');
            return $subtotal* $vat;
        }            
        return $subtotal;
    }
    public static function getSubtotal($orderId){
        return number_format((self::getSubtotalUnformat($orderId)+self::getDeliveryPrice($orderId)+self::getTransactionFee($orderId)),2,",",".");
    }
    public static function getVat($orderId){
        $vat = '0.'.Cfg::getPref('btw');
        return number_format((self::getSubtotalUnformat($orderId)+self::getDeliveryPrice($orderId)+self::getTransactionFee($orderId))*$vat,2,",",".");
    }
    public static function getTotal($orderId,$inCents=false){
        $vat = '1.'.Cfg::getPref('btw');
        $out = number_format((self::getSubtotalUnformat($orderId)+self::getDeliveryPrice($orderId)+self::getTransactionFee($orderId))*$vat,2,",",".");
        if($inCents)
            return preg_replace('/[^0-9]+/','',$out);
        return $out;
    }

}

