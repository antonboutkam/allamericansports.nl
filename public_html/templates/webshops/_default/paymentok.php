<?php
class Paymentok{
    function  run($params){                
        Shoppingbasket::clear();              
        
        $psp = new Psp;        

        if(isset($params['transactionid']))
        {
            $iOrderID = $params['transactionid'];
            $sTrxid = $params['transactionid'];
            $paymentOk = $psp->checkPayment($params['transactionid']);
        }
        else
        {
            $paymentOk = $psp->checkPayment($params['trxid']);
            $iOrderID = $params['orderID'];
            $sTrxid = $params['trxid'];
        }


        
        if($paymentOk){                       
            $params['tracking_code'] = Analytics::getTrackingCode($iOrderID);
            $params['order'] = OrderDao::getOrder($iOrderID);
               
            Mailer::sendPaymentMail($params, $iOrderID);
            OrderDao::markPaid(null, $iOrderID);
            OrderDao::setProp($params['order'],'trxid', quote($sTrxid));
            
            $params['cms'] = WebshopCms::getPageByTag($params['current_webshop_id'], 'payment_ok', true);
        }else{
            
            $params['cms'] = WebshopCms::getPageByTag($params['current_webshop_id'], 'payment_notok', true);
        }

        $params              = Webshop::doFirst($params);                                                
        $params['content']   = parse('paymentok',$params);        
        return $params;
    }    
    
}