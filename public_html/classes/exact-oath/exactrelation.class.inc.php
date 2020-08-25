<?php
class ExactRelation extends ExactService{

    public function upload($iRelationId){

        $sExactRelationXml = self::makeXml($iRelationId);
        $sResponseXml = $this->getApi()->sendRequest('upload','Topic=Accounts&_Division_='.$this->getDivision(), 'post', $sExactRelationXml);
        $responseArray = self::xml2Array($sResponseXml);

        $sExactId = quote((string)$responseArray['Messages']['Message']['Topic']['Data']['@attributes']['keyAlt']);
        $iRelationId = (int)$iRelationId;
        $sQuery = "UPDATE relations SET exact_id='$sExactId' WHERE id=$iRelationId";
        query($sQuery,__METHOD__);
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

