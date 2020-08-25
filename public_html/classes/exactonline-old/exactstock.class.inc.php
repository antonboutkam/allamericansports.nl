<?php
class ExactStock extends ExactBase{

    public static function getStockByNuiCartId($productId){
        $ean = ProductDao::getProductPropBy('id',$productId,'ean');
        return self::getStockByEan($ean,$productId);
    }
    public static function storeExactStockLocally($id,$stock){
        $sql = sprintf('
                UPDATE
                    catalogue
                 SET
                    exact_stock=%d,
                    exact_lastcheck=NOW()
                 WHERE
                    id=%d',$stock,$id);

        query($sql,__METHOD__);
    }
    /**
     * @param $productId only used for loggin. If null we query the database for the id.
     *
     */
    public static function getStockByEan($ean,$productId=null){
        if($productId==null)
            $productId = ProductDao::getProductPropBy('ean',$ean,'ean');
        $ch = self::curlConnect();

        $url = self::$baseurl."/docs/XMLDownload.aspx?Topic=StockPositions&Params_Code=".$ean."&PartnerKey=".self::$partnerkey."&UserName=".self::$username."&Password=".self::$password;
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        #echo "result=$result";
        /* Finally close as we're finished with this session */
        self::logTransaction($productId,'out','getstock');
        curl_close($ch);
        $xmlParsed =  simplexml_load_string($result);
        self::logTransaction($productId,'in',$result);

        // Mogelijk werkt dit niet goed als een product meerdere voorraden heeft op meerdere locaties in het magazijn.
        return (int)$xmlParsed->StockPositions->StockPosition->CurrentQuantity;
    }

	public static function genStock(){
        $sql = 'SELECT ean, exact_stock FROM catalogue'; //#AB er uti gehaald WHERE exact_stock>0
		$res = mysql_query($sql);
		$xml .= '<?xml version="1.0" encoding="utf-8"?>'."\r\n";
		$xml .= '<eExact xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="eExact-XML.xsd">'."\r\n";
        $c = 1;
        $batchBlockCount = 1;
        $stockCountLine = 1;
        $stockCount = 1;
        $xml .= '<StockCounts>'."\r\n";
        $xml .= '	<StockCount StockCountNumber="'.$stockCount.'">' ."\r\n";
        while($rows[] = mysql_fetch_assoc($res)){

        }

        $rowCount = count($rows);
		foreach($rows as $row){
  		    if((int)$row['exact_stock']==0){
				status($c." -Voorraad is nul, overslaan ".$row['ean'].".");
				continue;
			}
            #$row['ean']='883623818784';
            status($c." - Get product ".$row['ean']." from Exact for the Unit code");
            $prodXmlParsed  = ExactProduct::getByEan($row['ean']);
            $unitCode       = $prodXmlParsed->Items->Item->Sales->Unit['code'];
			status($c." unit code is ".$unitCode." exact stock is ".$row['exact_stock']);
		    if(empty($unitCode)){
				status($c." - Get product ".$row['ean']." komt niet voor in Exact.");
				#print_r($prodXmlParsed);
				continue;
			}

			$xml .= '	<StockCountLine LineNumber="'.$stockCountLine.'">'."\r\n";
            $stockCountLine++;
			$xml .= '		<NewQuantity>'.$row['exact_stock'].'</NewQuantity>'."\r\n";
			$xml .= '		<Item code="'.$row['ean'].'">'."\r\n";
			$xml .= '			<Description />'."\r\n";
			$xml .= '		</Item>'."\r\n";

			$xml .= '		<Unit code="'.$unitCode .'" />'."\r\n";
			$xml .= '		<GLAccount code="7030">'."\r\n";
			$xml .= '			<Description>Inkopen overig NL BTW</Description>'."\r\n";
			$xml .= '		</GLAccount>'."\r\n";
			$xml .= '		<Warehouse code="1">'."\r\n";
			$xml .= '			<Description>Magazijn</Description>'."\r\n";
			$xml .= '		</Warehouse>'."\r\n";
			$xml .= '		</StockCountLine>'."\r\n";

            $batchBlockCount++;
            #echo "batchBlockCount:".$batchBlockCount.PHP_EOL;
            if($batchBlockCount>=105 && $rowCount>($c-160)){
                $batchBlockCount = 1;
                $stockCount ++;
                $stockCountLine =1 ;
                echo "Next Batch".PHP_EOL;
                $xml .= '	</StockCount>'."\r\n";
                $xml .= '	<StockCount StockCountNumber="'.$stockCount.'">' ."\r\n";

            }


            $c++;

		}
        $xml .= '	</StockCount>'."\r\n";
        $xml .= '  </StockCounts>'."\r\n";
		$xml .= '	</eExact>'."\r\n";
		file_put_contents('nui-to-exact-manual-import-file.xml',$xml);

		return $result;

    }

}    