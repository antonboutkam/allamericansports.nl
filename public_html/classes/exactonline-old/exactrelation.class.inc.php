<?php
class ExactRelationOLD extends ExactBase {
    public static function upload($relationId){

        $xml                = self::makeXml($relationId);        
        self::logTransaction($productId,'out',$xml['no_binary']);
        $response           = ExactUpload::uploadXml($xml,'Accounts');
        $responseArray      = self::xml2Array($response);
        $exact_id           = $responseArray['Messages']['Message']['Topic']['Data']['@attributes']['keyAlt'];
        $tpl = 'UPDATE relations SET exact_id=%d WHERE id=%d';
        $sql = sprintf($tpl,$exact_id,$relationId);
        // echo $sql; 
        query($sql,__METHOD__);
                                
        /*
        
        self::logTransaction($productId,'in',$response);        
                
                
        query(sprintf('UPDATE catalogue SET exact_last_sync = NOW(), exact_synced=1 WHERE id=%d',$productId),__METHOD__);
        
        // 0 error , 1=warning, 2=succes, 3=fatal
        if($responseArray['Messages']['Message']['@attributes']['type']!=2){            
            ProductDao::setVal('exact_last_sync_fail',1,$productId);     
            $msg[] = "Error product id $productId";
            $msg[] = "Error melding \"".$responseArray['Messages']['Message']['Description'].'"'; 
            if(!isset($_SERVER['HTTP_HOST']))            
                echo join(",",$msg).PHP_EOL;
            // mail(Cfg::getPref('exact_online_status_mails'),'Allamericansports product '.$productId.' kon niet worden bijgewerkt in Exact',join(PHP_EOL,$msg));       
        }else{
            ProductDao::setVal('exact_last_sync_fail',0,$productId);
        }        
        ProductDao::setVal('exact_last_sync_desc ',$responseArray['Messages']['Message']['Description'],$productId);             
        */
    }
    public static function uploadXml($xml,$topic='Items'){
        $ch = self::curlConnect();
        $url = self::$baseurl."/docs/XMLUpload.aspx?Topic=".$topic."&PartnerKey=".self::$partnerkey."&UserName=".self::$username."&Password=".self::$password;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, utf8_encode($xml));
        $result = curl_exec($ch);
        #echo $xml;
        #echo "\n------------\n";
        #echo $result;
        #print_r(self::xml2Array($result));
        curl_close($ch);
        return $result;
    }

    static function makeXml($relationId){
        $relation = RelationDao::getById($relationId);
        
        
        $xml[] = '<?xml version="1.0" encoding="utf-8"?>';
        $xml[] = '<eExact xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="eExact-XML.xsd">';
        $xml[] = '  <Accounts>';
        if($relation['exact_id']){
            $xml[] = '      <Account code="'.$relation['exact_id'].'" searchcode="" status="C">';
        }else{
            $xml[] = '      <Account searchcode="" status="C">';
        }                        
        $xml[] = '      <Name>'.$relation['cp_firstname'].' '.$relation['cp_lastname'].'</Name>';
        $xml[] = '      <Phone>0653377030</Phone>';
        $xml[] = '      <PhoneExt />';
        $xml[] = '      <Fax />';
        if($relation['exact_id']){
            $xml[] = '      <Email>'.$relation['email'].'</Email>';
        }else{
            $xml[] = '      <Email />';            
        }        
        $xml[] = '      <HomePage />';
        $xml[] = '      <IsSupplier>0</IsSupplier>';
        $xml[] = '      <CanDropShip>0</CanDropShip>';
        $xml[] = '      <IsBlocked>0</IsBlocked>';
        $xml[] = '      <IsReseller>0</IsReseller>';
        $xml[] = '      <IsSales>1</IsSales>';
        $xml[] = '      <IsPurchase>1</IsPurchase>';
        $xml[] = '      <Address type="VIS" default="1">';
        $xml[] = '          <AddressLine1>'.$relation['billing_street'].' '.$relation['billing_number'].'</AddressLine1>';
        $xml[] = '          <AddressLine2 />';
        $xml[] = '          <AddressLine3 />';
        $xml[] = '          <PostalCode>'.$relation['billing_postal'].'</PostalCode>';
        $xml[] = '          <City>'.$relation['billing_city'].'</City>';
        $xml[] = '          <State/>';
        $xml[] = '          <Country/>';
        $xml[] = '          <Phone />';
        $xml[] = '          <Fax />';
        $xml[] = '      </Address>';
        $xml[] = '      <VATNumber />';
        $xml[] = '      <VATLiability />';
        $xml[] = '      <ChamberOfCommerce />';
        $xml[] = '      <PurchaseCurrency code="EUR" />';
        $xml[] = '      <CreditLine>';
        $xml[] = '          <Sales>0</Sales>';
        $xml[] = '          <Purchase>0</Purchase>';
        $xml[] = '      </CreditLine>';
        $xml[] = '      <Discount>';
        $xml[] = '          <SalesPercentage>0</SalesPercentage>';
        $xml[] = '          <PurchasePercentage>0</PurchasePercentage>';
        $xml[] = '      </Discount>';
        $xml[] = '      <AccountClassifications />';
        $xml[] = '      <IsMailing>0</IsMailing>';
        $xml[] = '      <IsCompetitor>0</IsCompetitor>';
        $xml[] = '      <StartDate>2013-11-17</StartDate>';
        $xml[] = '      <IntraStat>';
        $xml[] = '      <System />';
        $xml[] = '      <TransactionA />';
        $xml[] = '      <TransactionB />';
        $xml[] = '      <TransportMethod />';
        $xml[] = '      <DeliveryTerm />';
        $xml[] = '      <Area />';
        $xml[] = '      </IntraStat>';
        $xml[] = '      <InvoicingMethod>0</InvoicingMethod>';
        $xml[] = '  </Account>';
        $xml[] = '</Accounts>';
        $xml[] = '</eExact>';  
        return join(PHP_EOL,$xml);
         
    }    
    
 
            
}