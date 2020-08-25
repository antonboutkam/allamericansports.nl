<?php
class ExactOrder extends ExactService{
    
    public function upload($iFkOrder){

        $sExactOrderXml = self::makeXml($iFkOrder);
        ExactXmlLog::logOrderXml($iFkOrder, 'out', $sExactOrderXml);

        $sResponseXml = $this->getApi()->sendRequest('upload','Topic=SalesOrders&_Division_='.$this->getDivision(), 'post', $sExactOrderXml);
        ExactXmlLog::logOrderXml($iFkOrder, 'in', $sResponseXml);

        $oResponse = simplexml_load_string($sResponseXml);

        foreach($oResponse->Messages->Message as $oMessage){
            $sNodeName = (string)$oMessage->Topic['node'];
           if($sNodeName == 'SalesOrder'){
                $iExactOrderId = (string)$oMessage->Topic->Data['key'];
                $sql = "UPDATE orders
                        SET exact_salesorder=$iExactOrderId,
                            exact_salesregisterd=NOW(),
                            exact_stat='complete'
                            WHERE id=$iFkOrder";

                query($sql,__METHOD__);
            }
        }

        return null;
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

        // Salesordernumber wordt geretouneerd door exact en opgeslagen in de orders table.
        // Als de order later gewijzigd wordt dan is "exact_salesorder" gevuld waardoor het script een update doet.
        // Status moet bij een import altijd 12 zijn
        if(!empty($order['exact_salesorder'])){
            $xml[] = '<SalesOrder salesordernumber="'.$order['exact_salesorder'].'" status="12">';
        }else{
            $xml[] = '<SalesOrder status="12">';
        }
        $xml[] = '<OrderDate>'.$order['order_date_format'].'</OrderDate>';
        $xml[] = '<DeliveryDate>'.$order['order_date_format'].'</DeliveryDate>';
        $xml[] = '<Description>Uw bestelling bij Allamericansports met ordernummer '.$orderId.'</Description>';
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

        return join("\r\n",$xml);
    }



}

