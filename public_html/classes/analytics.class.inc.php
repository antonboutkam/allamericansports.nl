<?php
class Analytics{
    /**
    * @description This function returns the Google analytics tracking code for a succesfull sale.
    * @author Anton Boutkam
    * @param orderId
    * @return strring with javascript
    */
    public static function getTrackingCode($orderId){
        $params['done_order']           =  OrderDao::getOrder($orderId);
        $tmp['return_totals_no_pdf']    = true;
        $tmp['orderid']                 = $orderId;
        $params['totals']               = Billgen::run($tmp);        
        $params['done_order_items']     = OrderDao::getOrderItems($orderId);               
        $vat                            = (($params['totals']/100)*0.21);
        
        $out    = array();
        $out[]  = "<script>";
        $out[]  = "ga('ecommerce:clear');";
        $out[]  = "ga('ecommerce:addTransaction', {";
        $out[]  = "     id            : '".$orderId."', ";          
        $out[]  = "     affiliation   : 'allamericansports.nl',";
        $out[]  = "     revenue       : '".number_format($params['totals'],2,'.','')."',";                           
        $out[]  = "     currency        : 'EUR',";  // local currency code.
        $out[]  = "     tax           : '".$vat."'";                              
        $out[]  = "});";

        if(!empty($params['done_order_items'])){
            foreach($params['done_order_items']['data'] as $item){
                $out[]  = "ga('ecommerce:addItem', {";
                $out[]  = "     id          : '".$orderId."',";
                $out[]  = "     name        : '".str_replace('"',"",$item['article_name'])."',";
                $out[]  = "     sku         : '".str_replace('"',"",$item['ean'])."',";                
                $out[]  = "     price       : '".number_format($item['sale_price']*1.21,2,".","")."',";                 // Unit price.
                $out[]  = "     quantity    : '".$item['quantity']."'";                   // Quantity.
                $out[]  = "});";                    
            }
        }
        $out[]  = "ga('ecommerce:send');";
        
        
        $out[]  = "</script>";
        // mail('anton@nui-boutkam.nl','Allamericansports transaction',join(PHP_EOL,$out));    
        if($_SERVER['IS_DEVEL']){
            // Geen Google analytics transacties tracken op dev!
            return '';                        
        }        
        return join(PHP_EOL,$out);                        
    }
}