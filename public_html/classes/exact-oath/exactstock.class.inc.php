<?php
class ExactStock extends ExactService{

    public function getStock($iFkProduct){
        $sProductEanCode =  ProductDao::getProductPropBy('id', $iFkProduct, 'ean');
        $sResponseXml = $this->getApi()->sendRequest('download','Topic=StockPositions&Params_Code='.$sProductEanCode.'&_Division_='.$this->getDivision(), 'get');


        $xmlParsed =  simplexml_load_string($sResponseXml);

        $iExactQuantity = (int)$xmlParsed->StockPositions->StockPosition->CurrentQuantity;

        $this->storeExactStockLocally($iFkProduct, $iExactQuantity);
        return $iExactQuantity;
    }
    public static function storeExactStockLocally($iId, $iStockQuantity){
        $sql ="UPDATE
                    catalogue
                 SET
                    exact_stock=$iStockQuantity,
                    exact_lastcheck=NOW()
                 WHERE
                    id=$iId";

        query($sql,__METHOD__);
    }

    public static function getStockByNuiCartId($productId){
        $ean = ProductDao::getProductPropBy('id',$productId,'ean');
        return self::getStockByEan($ean,$productId);
    }

    /**
     * @param $productId only used for loggin. If null we query the database for the id.
     *
     */
    /*
    public static function getStockByEan($ean,$productId=null){
        if($productId==null)
            $productId = ProductDao::getProductPropBy('ean',$ean,'ean');
        $ch = self::curlConnect();

        $url = self::$baseurl."/docs/XMLDownload.aspx?Topic=StockPositions&Params_Code=".$ean."&PartnerKey=".self::$partnerkey."&UserName=".self::$username."&Password=".self::$password;
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        #echo "result=$result";

        self::logTransaction($productId,'out','getstock');
        curl_close($ch);
        $xmlParsed =  simplexml_load_string($result);
        self::logTransaction($productId,'in',$result);

        // Mogelijk werkt dit niet goed als een product meerdere voorraden heeft op meerdere locaties in het magazijn.
        return (int)$xmlParsed->StockPositions->StockPosition->CurrentQuantity;
    }
*/

}

