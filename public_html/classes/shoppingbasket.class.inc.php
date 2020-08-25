<?php
class Shoppingbasket {
    static $paymethodInfo;
    public static function getPaymethodById($id){        
        if(!isset(self::$paymethodInfo[$id]))
            self::$paymethodInfo[$id] = Paymethod::getById($id);
        return  self::$paymethodInfo[$id];
    }
    public static function clear(){    
        Log::message('baket_clear','Shopping basket cleared, goto paypge.',__METHOD__);
        unset($_SESSION['basket'],$_SESSION['delivery'],$_SESSION['paymethod']);        
    }
    public static function hasDelivery(){
        
        return $_SESSION['delivery_yn'];
    }
    /**
     * @param $delivery (boolean, will the client come and pick up the order?
     * @param $internationalOrder (boolean, for international orders we have a special tarif)
     */
    public static function setDelivery($hostname,$delivery,$internationalOrder=false){                    
        $shippingCostClass               = Webshop::getWebshopSetting($hostname, 'ship_cost_calc_method');                
        $shippingCostClass               = ($shippingCostClass)?$shippingCostClass:'Nocost';        
        $shippingCostObject              = new $shippingCostClass;
                
        $_SESSION['delivery']            = $shippingCostObject->calcShippingCost($hostname,$delivery,$internationalOrder,self::getBasket());
                
        $_SESSION['international_order'] = $internationalOrder;
        $_SESSION['delivery_set']        = true;
        $_SESSION['delivery_yn']         = $delivery;               

    }
    public static function setBank($bank){
        $_SESSION['paymethod_bank'] = $bank;
    }
    public static function getBank(){
        return $_SESSION['paymethod_bank'];
    }    
    public static function setPaymethod($paymethod){
        $_SESSION['paymethod'] = $paymethod;
    }
    public static function getPaymethodValue(){
        if(!isset($_SESSION['paymethod']))
            return 0;
        else
            $method = self::getPaymethodById($_SESSION['paymethod']);
            return $method['price_amount'];        
    }
    public static function getPaymethodCalctype(){
        if(!isset($_SESSION['paymethod']))
            return 'free';
        else
            $method = self::getPaymethodById($_SESSION['paymethod']);
            return $method['price_type'];        
    }
    public static function getPaymethod(){
        return $_SESSION['paymethod'];
    }
    public static function getDeliveryPrice($numberFormat=false,$addVat=false){
        $delivery = 0;
        if(isset($_SESSION['delivery'])){
            $delivery = $_SESSION['delivery'];
        }
        if($addVat){                        
            $vat = '0.'.Cfg::getPref('btw');
            $delivery = $delivery + ($delivery * (float)$vat);            
        }
        if($numberFormat)
            return number_format($delivery,2,",",".");
        return $delivery;
    }
    public static function getTransactionFee(){
        $calctype    = self::getPaymethodCalctype();
        
        if($calctype=='free')
            return '0';
        else{
            $paymethodvalue = self::getPaymethodValue();
            $subtotal       = self::getSubtotalUnformat()+self::getDeliveryPrice();

            if($calctype=='percentage'){
                return ($subtotal/100)*$paymethodvalue;
            }else{                
                return $paymethodvalue;
            }
        }
    }
    public static function getTransactionFeeVis($incVat=false){
        $out = self::getTransactionFee();
        if($incVat){            
            $vat = '1.'.Cfg::getPref('btw');            
            return number_format($out*(float)$vat,2,",",".");
        }
        return number_format($out,2,",",".");
    }
    public static function addProduct($productId,$quantity){
        if($quantity<=0){
            unset($_SESSION['basket'][$productId]);
            return;
        }    
        if(!isset($_SESSION['basket'][$productId])){
            $_SESSION['basket'][$productId] = ProductDao::getById($productId);
            $_SESSION['basket'][$productId]['order_quantity'] = $quantity;
        }else{
            $_SESSION['basket'][$productId]['order_quantity'] += $quantity;
        }            
    }

    public static function getBasket(){        
        $vat = '1.'.Cfg::getPref('btw');        
        if(isset($_SESSION['basket']) && is_array($_SESSION['basket']))
            foreach($_SESSION['basket'] as $id=>$row){
                $_SESSION['basket'][$id]['article_short'] = $_SESSION['basket'][$id]['article_number'];
                if(strlen($_SESSION['basket'][$id]['article_number'])>15)
                    $_SESSION['basket'][$id]['article_short'] = substr($_SESSION['basket'][$id]['article_number'],0,13).'...';
                                
                $saleprice  = $row['sale_price'].'.'.$row['sale_price_ct'];
                if($row['is_offer']){
                    $saleprice = ($saleprice/100)*(100-$row['discount']);  
                }
                
                $_SESSION['basket'][$id]['subtotal_vis']        =  number_format((float)$saleprice * $row['order_quantity'],2,",",".");
                
                $incvat = ($saleprice*$row['order_quantity']) * $vat;
                $_SESSION['basket'][$id]['subtotal_vis_vat']    = number_format($incvat,2,",",".");
                $_SESSION['basket'][$id]['sale_price_vis']      = number_format((float)$saleprice ,2,",",".");
        }
        if(isset($_SESSION['basket'])){
            return $_SESSION['basket'];
        }
        return null;
    }
	
    
    
    public static function getDiscount($incVat=true){                                
        if(!empty($_SESSION['discount'])){           
            if($_SESSION['discount']['type']=='perc'){
                if($incVat){
                    $vat = '1.'.Cfg::getPref('btw');
                    $subtotal = (float)self::getSubtotalUnformat()*(float)$vat;
                    
                }else{
                    $subtotal = self::getSubtotalUnformat();
                }
                $fee = ($subtotal/100)*$_SESSION['discount']['amount'];
            }else{
                $fee = $_SESSION['discount']['amount'];
            }            
        }
        
        $msg = 'fee: '.$fee."\n".print_r($_SESSION['discount'],true)."\n".$subtotal;
        #mail('anton@freelancephpprogrammeur.nl','sbutotal_fee_rest',$msg);
        return $fee;
    }
    public static function getDiscountProps(){
        return $_SESSION['discount'];
    }
	
    public static function getSubtotalUnformat($incVat =false){
        $subtotal = 0;
        if(isset($_SESSION['basket']) && is_array($_SESSION['basket']))
            foreach($_SESSION['basket'] as $id=>$row){
                $saleprice  = $row['sale_price'].'.'.$row['sale_price_ct'];
                $subtotal = $subtotal + ((float)$saleprice * $row['order_quantity']);
            }
        if($incVat){
            $vat = '1.'.Cfg::getPref('btw');
            return $subtotal*$vat;
        }              
        return $subtotal;
    }
    public static function getSubtotal($incVat = false){
        $exVat = self::getSubtotalUnformat()+self::getTransactionFee();
        if($incVat){
            $vat = '1.'.Cfg::getPref('btw');    
            return number_format($exVat*$vat,2,",",".");    
        }            
        return number_format($exVat,2,",",".");
    }
    
    public static function getVat(){
        $vat    = '1.'.Cfg::getPref('btw');        
        $exVat  = '0.'.Cfg::getPref('btw');
        /*
        echo self::getSubtotalUnformat()."<br>";
        echo $_SESSION['delivery']."<br>";
        echo self::getTransactionFee()."<br>";
        echo 'vat: '.$vat."<br>";
        echo 'total: '.((self::getSubtotalUnformat()+$_SESSION['delivery']+self::getTransactionFee())*$vat)."<br>";         
         */
        $fDelivery = 0;
        if(isset($_SESSION['delivery'])){
            $fDelivery = $_SESSION['delivery'];
        }

        $totalExVat     = (self::getSubtotalUnformat() + $fDelivery + self::getTransactionFee());
        $totalIncVat    = $totalExVat * (float)$vat;
        #echo 'ex vat '.$totalExVat.' inc vat '.$totalIncVat;    
        return number_format($totalIncVat - $totalExVat,2,",",".");
        
        //return number_format((self::getSubtotalUnformat()+$_SESSION['delivery']+self::getTransactionFee())*$vat,2,",",".");
    }
    public static function getTotal(){
        $vat = '1.'.Cfg::getPref('btw');
        $fDelivery = 0;
        if(isset($_SESSION['delivery'])){
            $fDelivery = $_SESSION['delivery'];
        }


        return number_format((self::getSubtotalUnformat() + $fDelivery + self::getTransactionFee())*$vat,2,",",".");
    }    
    
    /**
     * Shoppingbasket::getTotalQuantity()
     * Geeft het totaal aantal producten in de winkelwagen terug. 
     * @return
     */
    public static function getTotalQuantity(){
        $quantity = 0;
        if(isset($_SESSION['basket']) && is_array($_SESSION['basket'])){
            foreach($_SESSION['basket'] as $id=>$row){
                $quantity += $row['order_quantity'];
            }
        }
        return $quantity;
    }
    public static function update($data){
        $_SESSION['basket'] = null;        
        foreach($data['cart'] as $productId=>$quantity)
            self::addProduct($productId, $quantity);        
    }
    public static function remove($id){
        unset($_SESSION['basket'][$id]);
    }
    public static function getCart($params){
        $params['cart_items']       = self::getTotalQuantity();
        $params['basket']           = self::getBasket();        
        
        $params['subtotal']         = self::getSubtotal();
        $params['subtotal_incvat']  = self::getSubtotal(true);        
        
        Log::console("Subtotal ".$params['subtotal']);
        
        $params['total']            = self::getTotal();
        $params['transaction']      = self::getTransactionFeeVis();
        $params['transaction_vat']  = self::getTransactionFeeVis(true);
        
        
        $params['has_transaction']  = (int)str_replace(",","",$params['transaction']);;
        $params['delivery']         = self::getDeliveryPrice(true);
                
        $params['delivery_vat']     = self::getDeliveryPrice(true,true);
        
        $params['has_delivery']     = (int)str_replace(",","",$params['delivery']);
        
        
        $params['vat']              = self::getVat();
        $params['total_quantity']   = self::getTotalQuantity();    
        return $params;
    }     
}


