<?php
class ExactOrderOld extends ExactBase{
    static function sendToExactOnline($orderId){
        
        $xml = self::makeXml($orderId);
        
        #echo $xml; 
        ExactXmlLog::logOrderXml($orderId,'out',$xml);        

        $responseXml  = self::sendOrder($xml,$orderId,true);
        $response = simplexml_load_string($responseXml);

        foreach($response->Messages->Message as $message){
            
            if((string)$message->Topic['node']=='Account'){
                
            }else if((string)$message->Topic['node']=='SalesOrder'){
                if(!$exactOrderId){
                    mail(Cfg::get('ERROR_MAILER'),'Order '.$orderId.' niet goed in exact verwerkt',$responseXml);
                }
                #pre_r($message->Topic);
                $exactOrderId = (int)$message->Topic->Data['key'];                
                $sql = sprintf('UPDATE orders 
                                SET exact_salesorder=%d, 
                                    exact_salesregisterd=NOW(),
									exact_stat="complete"
                                WHERE id=%d',$exactOrderId,$orderId);
                // mail('info@nuicart.nl','exact order',$sql);
                #echo nl2br($sql)."\n\n";                              
                query($sql,__METHOD__);                              
            }         
        }        
        
        return $responseArr;

/*
		#echo $response.PHP_EOL.PEP_EOL;
        #echo "<h1>Message</h1>";
        #echo "<pre>".htmlentities($xml)."</pre><<br><br><br>";
        #echo "<h1>Response</h1>";
        #echo "<pre>".htmlentities($response)."</pre><br><br><br>";
        
        
        $response = simplexml_load_string($response);
        
        foreach($response->Messages->Message as $message){
            if((string)$message->Topic['node']=='Account'){
                
            }else if((string)$message->Topic['node']=='SalesOrder'){
                $exactOrderId = (int)$message->Topic->Data['key'];                
                $sql = sprintf('UPDATE orders 
                                SET exact_salesorder=%d, 
                                    exact_salesregisterd=NOW()
									exact_stat="complete"
                                WHERE id=%d',$exactOrderId,$orderId); 
                query($sql,__METHOD__);                              
            }         
        }
        
        
        return $response;
        */
    }
    function sendOrder($xml,$orderId,$returnRawResult=false){        
        $ch = self::curlConnect();
                
        $url = self::$baseurl."/docs/XMLUpload.aspx?Topic=SalesOrders&PartnerKey=".self::$partnerkey."&UserName=".self::$username."&Password=".self::$password;
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, utf8_encode($xml));
        $result = curl_exec($ch);
        ExactXmlLog::logOrderXml($orderId,'in',$result);                
        curl_close($ch);
        
        $resultArray =  self::xml2Array($result);
        foreach($resultArray['Messages']['Message'] as $message){      
            if(!is_array($message) || !isset($message['Topic'])){
                continue;
            }
            if($message['@attributes']['type']!=2){
                $message[] = "Bericht heen\n\n";
                $message[] = $xml;
                $message[] = "Bericht terug\n\n";
                $message[] = $result;
                mail(Cfg::getPref('exact_online_status_mails'),'Allamericansports er is iets mis gegaan met order'.$orderId,join(PHP_EOL,$message));
            }                
        }    
        if($returnRawResult){
            return $result;
        }else{                 
            return $resultArray;
        }
        /* 
        foreach($resultArray['Messages']['Message'] as $message){      
            if(!is_array($message) || !isset($message['Topic'])){
                continue;
            }
            
            $eanCode = $message['Topic']['Data']['@attributes']['keyAlt'];
            echo "ean ".$eanCode.PHP_EOL;
            if(!$eanCode){                
                pre_r($message);
            }
            $status  = $message['Description'];
            
             // 0 error , 1=warning, 2=succes, 3=fatal
            if($message['@attributes']['type']!=2){                       
                $failedProduct = ProductDao::getBy('ean',$eanCode,true);
                
                ProductDao::setVal('exact_last_sync_fail',1,$failedProduct['id']);
                                
                $msg[] = "Error product id $productId";
                $msg[] = "Error melding \"".$responseArray['Messages']['Message']['Description'].'"';     
                                                                             
                $productId = $failedProduct['id'];
                self::logTransaction($productId,'in','failed xml update');
                echo "$productId - $status this has been reported to ".Cfg::getPref('exact_online_status_mails').PHP_EOL;
                echo "response: ";
                #pre_r($message);                  
            }else{
                self::logTransaction($productId,'in','succesfull xml update');
                $successProduct = ProductDao::getBy('ean',$eanCode,true);
                ProductDao::setVal('exact_last_sync_fail',0,$successProduct['id']);
                $productId = $successProduct['id'];
            }
            ProductDao::setVal('exact_last_sync_desc ',$status,$productId);
                                   
        }
        if(is_array($msg)){
            mail(Cfg::getPref('exact_online_status_mails'),'Allamericansports product '.$productId.' kon niet worden bijgewerkt in Exact',join(PHP_EOL,$msg));
        }                    
        return $result;
        */
    }       
    
    private static function makeXml($orderId){
        $order                                  =   OrderDao::getOrder($orderId);
                                 
        $order_from                             =   Webshop::getWebshopSetting($order['hostname'],'company_name');        
        $tmp['return_totals_no_pdf_inc_vat']    =   true;
        $tmp['orderid']                         =   $orderId;
   //     $totals                                 =   Billgen::run($tmp);
        $order_items                            =   OrderDao::getOrderItems($orderId);
        $vat                                    =   1+(Cfg::getPref('btw')/100);
        $vat_perc                               =   Cfg::getPref('btw');
        $send_method                            =   ($order['delivery_pickup'] == 'delivery')?'Verzenden per post':'Klant komt het product ophalen';
                        
        $xml[] = '<?xml version="1.0" encoding="utf-8"?>';
        $xml[] = '<eExact xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="eExact-XML.xsd">';
        $xml[] = '<SalesOrders>';
        
        // Aalesordernumber wordt geretouneerd door exact en opgeslagen in de orders table.
        // Als de order later gewijzigd wordt dan is "exact_salesorder" gevuld waardoor het script een update doet.
        // Status moet bij een import altijd 12 zijn        
        if(!empty($order['exact_salesorder'])){
            $xml[] = '<SalesOrder salesordernumber="'.$order['exact_salesorder'].'" status="12">';
        }else{
            $xml[] = '<SalesOrder status="12">';
        }
        $xml[] = '<OrderDate>'.$order['order_date_format'].'</OrderDate>';
        $xml[] = '<DeliveryDate>'.$order['order_date_format'].'</DeliveryDate>';
        $xml[] = '<Description>Uw bestelling bij '.$order_from.'</Description>';        
#        $xml[] = '<SalesPerson>A.A. Fasting</SalesPerson>'; 
        
#        $xml[] = '<SalesPerson>'; 
#        $xml[] = '  <FullName>A.A. Fasting</FullName>'; 
#        $xml[] = '</SalesPerson>'; 
        
        $xml[] = '          <YourRef>'.$order['order_id_vis'].'</YourRef>';
        $xml[] = '            <OrderedBy code="'.$order['relation_exact_id'].'">';
        $xml[] = '               <Name>'.htmlspecialchars($order['company_or_person']).'</Name>';
        $xml[] = '               <Phone>'.htmlspecialchars($order['phone']).'</Phone>';
        $xml[] = '            </OrderedBy>';
        $xml[] = '            <DeliverTo>';
        $xml[] = '                <Name>'.htmlspecialchars($order['company_or_person']).'</Name>';
        $xml[] = '                <Phone>'.htmlspecialchars($order['phone']).'</Phone>';
        $xml[] = '            </DeliverTo>';
        
        $xml[] = '            <DeliveryAddress>';
        if(!empty($order['shipping_street']) && !empty($order['shipping_number']) && !empty($order['shipping_postal'])){        
            
            $xml[] = '              <AddressLine1>'.htmlspecialchars($order['shipping_street']).' '.htmlspecialchars($order['shipping_number']).'</AddressLine1>';								
            $xml[] = '              <PostalCode>'.htmlspecialchars($order['shipping_postal']).'</PostalCode>';
            $xml[] = '              <City>'.htmlspecialchars($order['shipping_city']).'</City>';    
            if($order['shipping_country'] == 'The Netherlands'){    
                $xml[] = '              <Country code="NL" />';
            }                             
        }else{            
            $xml[] = '              <AddressLine1>'.htmlspecialchars($order['billing_street']).' '.htmlspecialchars($order['billing_number']).'</AddressLine1>';								
            $xml[] = '              <PostalCode>'.htmlspecialchars($order['billing_postal']).'</PostalCode>';
            $xml[] = '              <City>'.htmlspecialchars($order['billing_city']).'</City>';    
            if($order['billing_country'] == 'The Netherlands'){    
                $xml[] = '              <Country code="NL" />';
            }                 
                                
        }
        
		$xml[] = '                <Contact>';
		$xml[] = '                      <LastName>'.htmlspecialchars($order['cp_lastname']).'</LastName>';        
		$xml[] = '                      <FirstName>'.htmlspecialchars($order['cp_firstname']).'</FirstName>';		
        $xml[] = '                      <FullName>'.htmlspecialchars($order['cp_firstname']).' '.htmlspecialchars($order['cp_lastname']).'</FullName>';
        $xml[] = '                      <Email>'.htmlspecialchars($order['email']).'</Email>';
		$xml[] = '                </Contact>';
        $xml[] = '            </DeliveryAddress>';
        

        
        $xml[] = '            <InvoiceTo>';
        $xml[] = '                <Name>'.htmlspecialchars($order['company_or_person']).'</Name>';
        $xml[] = '                <Phone>'.htmlspecialchars($order['phone']).'</Phone>';
        $xml[] = '            </InvoiceTo>';
#        $xml[] = '            <PaymentCondition code="21">';
#        $xml[] = '                <Description>21 dagen</Description>';
#        $xml[] = '            </PaymentCondition>';
/* 24042014 tijdelijk uit
        $xml[] = '            <ForeignAmount>';
        $xml[] = '                <Currency code="EUR" />';
        $xml[] = '                <Value>'.$totals.'</Value>';
        $xml[] = '                <Rate>1</Rate>';
        $xml[] = '                <PaymentDiscountAmount>';
        $xml[] = '                    <Value>0</Value>';
        $xml[] = '                </PaymentDiscountAmount>';
        $xml[] = '            </ForeignAmount>';
*/        
        $xml[] = '            <ShippingMethod code="">';
        $xml[] = '                <Description>'.$send_method.'</Description>';
        $xml[] = '            </ShippingMethod>';
        $xml[] = '            <SalesPerson>';
        $xml[] = '                <FullName>'.htmlspecialchars($order_from).'</FullName>';
        $xml[] = '            </SalesPerson>';
        
        $x = 0;

        #pre_r($order_items);
        #exit();
                
        foreach($order_items['data'] as $item){
            #pre_r($item);
            #exit();
            $x++;
            $xml[] = '            <SalesOrderLine line="'.$x.'">';
            $xml[] = '                <Description>'.htmlspecialchars($item['article_name']).'</Description>';
            $xml[] = '                <Item code="'.htmlspecialchars($item['ean']).'">';
            $xml[] = '                    <Description>'.htmlspecialchars($item['article_name']).'</Description>';
#            $xml[] = '                    <IsSalesItem>1</IsSalesItem>';
#            $xml[] = '                    <IsStockItem>1</IsStockItem>';
/*
            $xml[] = '                    <Assortment code="Sportkleding">';
            $xml[] = '                        <Description>Sportkleding</Description>';
            $xml[] = '                        <IsDefault>0</IsDefault>';
            $xml[] = '                        <GLRevenue code="8010" type="110" balanceSide="C" balanceType="W">';
            $xml[] = '                            <Description>Verkopen sportkleding NL BTW</Description>';
            $xml[] = '                        </GLRevenue>';
            $xml[] = '                        <GLCosts code="7010" type="111" balanceSide="D" balanceType="W">';
            $xml[] = '                            <Description>Inkopen sportkleding NL BTW</Description>';
            $xml[] = '                        </GLCosts>';
            $xml[] = '                        <GLPurchase code="3310" type="40" balanceSide="D" balanceType="B">';
            $xml[] = '                            <Description>Voorraad sportkleding</Description>';
            $xml[] = '                        </GLPurchase>';
            $xml[] = '                        <GLPurchasePriceDifference code="3700" type="90" balanceSide="D" balanceType="B">';
            $xml[] = '                            <Description>Voorraadherwaardering</Description>';
            $xml[] = '                        </GLPurchasePriceDifference>';
            $xml[] = '                    </Assortment>';
*/            
            $xml[] = '                </Item>';
            $xml[] = '                <Quantity>'.$item['quantity'].'</Quantity>';
            $xml[] = '                <DeliveryDate>'.$order['order_date_format'].'</DeliveryDate>';
            $xml[] = '                <Unit code="stuk">';
            $xml[] = '                    <Description>Piece</Description>';
            $xml[] = '                </Unit>';
            $xml[] = '                <UnitPrice>';
            $xml[] = '                    <Currency code="EUR" />';
            $xml[] = '                    <Value>'.round(($item['sale_price']*$vat),2).'</Value>';
            $xml[] = '                    <VAT code="4">';
            $xml[] = '                        <Description>BTW '.$vat_perc.'%</Description>';
            $xml[] = '                    </VAT>';
            $xml[] = '                    <VATPercentage>0.'.$vat_perc.'</VATPercentage>';
            $xml[] = '                </UnitPrice>';
            $xml[] = '                <NetPrice>';
            $xml[] = '                    <Currency code="EUR" />';
            $xml[] = '                    <Value>'.($item['sale_price']*$item['quantity'])*$vat.'</Value>';
            $xml[] = '                    <VAT code="4">';
            $xml[] = '                        <Description>BTW hoog tarief, inclusief</Description>';
            $xml[] = '                    </VAT>';
            $xml[] = '                    <VATPercentage>0.'.$vat_perc.'</VATPercentage>';
            $xml[] = '                </NetPrice>';
/* 24042014 tijdelijk uit *
            $xml[] = '                <ForeignAmount>';
            $xml[] = '                    <Currency code="EUR" />';
            $xml[] = '                    <Value>74.26</Value>';
            $xml[] = '                    <Rate>1</Rate>';
            $xml[] = '                    <VATBaseAmount>74.26</VATBaseAmount>';
            $xml[] = '                    <VATAmount>15.59</VATAmount>';
            $xml[] = '                </ForeignAmount>';
*/            
            $xml[] = '                <DiscountPercentage>0</DiscountPercentage>';
            $xml[] = '                <UseDropShipment>0</UseDropShipment>';
            $xml[] = '            </SalesOrderLine>';    
        }
        if($order['send_cost'] && $order['send_cost']>0){
            /* VERZENDKOSTEN */
            $x++;
            $xml[] = '            <SalesOrderLine line="'.$x.'">';
            $xml[] = '                <Description>Verzendkosten</Description>';
            $xml[] = '                <Item code="1001">';
            $xml[] = '                    <Description>1001 Handelings- en verzendkosten</Description>';
            #$xml[] = '                    </Assortment>';        
            $xml[] = '                </Item>';
            $xml[] = '                <Quantity>1</Quantity>';
            $xml[] = '                <DeliveryDate>'.$order['order_date_format'].'</DeliveryDate>';
            $xml[] = '                <Unit code="pc">';
            $xml[] = '                    <Description>Piece</Description>';
            $xml[] = '                </Unit>';
            $xml[] = '                <UnitPrice>';
            $xml[] = '                    <Currency code="EUR" />';
            $xml[] = '                    <Value>'.round(($order['send_cost']*$vat),2).'</Value>';
            $xml[] = '                    <VAT code="4">';
            $xml[] = '                        <Description>BTW '.$vat_perc.'%</Description>';
            $xml[] = '                    </VAT>';
            $xml[] = '                    <VATPercentage>0.'.$vat_perc.'</VATPercentage>';
            $xml[] = '                </UnitPrice>';
            $xml[] = '                <NetPrice>';
            $xml[] = '                    <Currency code="EUR" />';
            $xml[] = '                    <Value>'.round(($order['send_cost']*$vat),2).'</Value>';
            $xml[] = '                    <VAT code="4">';
            $xml[] = '                        <Description>BTW hoog tarief, inclusief</Description>';
            $xml[] = '                    </VAT>';
            $xml[] = '                    <VATPercentage>0.'.$vat_perc.'</VATPercentage>';
            $xml[] = '                </NetPrice>';         
            $xml[] = '                <DiscountPercentage>0</DiscountPercentage>';
            $xml[] = '                <UseDropShipment>0</UseDropShipment>';
            $xml[] = '            </SalesOrderLine>';    
        }
        /* TRANSACTIE KOSTEN */
        if($order['pay_fee_fixed'] && $order['pay_fee_fixed']>0){
            $x++;
            $xml[] = '            <SalesOrderLine line="'.$x.'">';
            $xml[] = '                <Description>Transactiekosten</Description>';
            $xml[] = '                <Item code="1003">';
            $xml[] = '                    <Description>1003 Transactiekosten</Description>';
            #$xml[] = '                    </Assortment>';        
            $xml[] = '                </Item>';
            $xml[] = '                <Quantity>1</Quantity>';
            $xml[] = '                <DeliveryDate>'.$order['order_date_format'].'</DeliveryDate>';
            $xml[] = '                <Unit code="pc">';
            $xml[] = '                    <Description>Piece</Description>';
            $xml[] = '                </Unit>';
            $xml[] = '                <UnitPrice>';
            $xml[] = '                    <Currency code="EUR" />';
            $xml[] = '                    <Value>'.round(($order['pay_fee_fixed']*$vat),2).'</Value>';
            $xml[] = '                    <VAT code="4">';
            $xml[] = '                        <Description>BTW '.$vat_perc.'%</Description>';
            $xml[] = '                    </VAT>';
            $xml[] = '                    <VATPercentage>0.'.$vat_perc.'</VATPercentage>';
            $xml[] = '                </UnitPrice>';
            $xml[] = '                <NetPrice>';
            
            $xml[] = '                    <Currency code="EUR" />';
            $xml[] = '                    <Value>'.round(($order['pay_fee_fixed']*$vat),2).'</Value>';
            $xml[] = '                    <VAT code="4">';
            $xml[] = '                        <Description>BTW hoog tarief, inclusief</Description>';
            $xml[] = '                    </VAT>';
            $xml[] = '                    <VATPercentage>0.'.$vat_perc.'</VATPercentage>';
            $xml[] = '                </NetPrice>';         
            $xml[] = '                <DiscountPercentage>0</DiscountPercentage>';
            $xml[] = '                <UseDropShipment>0</UseDropShipment>';
            $xml[] = '            </SalesOrderLine>';           
        }
        
        
                
        $xml[] = '        </SalesOrder>';
        $xml[] = '    </SalesOrders>';
        $xml[] = '    <Messages />';
        $xml[] = '</eExact>';
        // windows line ending   
		#exit(join("\r\n",$xml));
		file_put_contents('./tmp/orderyeah.xml',join("\r\n",$xml));
        return join("\r\n",$xml);     
    }

}
