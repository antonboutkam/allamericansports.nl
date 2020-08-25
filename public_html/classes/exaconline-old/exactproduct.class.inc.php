<?php

class ExactProduct extends ExactBase {    
    public static function upload($productId){       
        
        $xml                = ExactXml::makeById($productId);        
        self::logTransaction($productId,'out',$xml['no_binary']);                                
        $response           = ExactUpload::uploadXml($xml['with_binary']);
        self::logTransaction($productId,'in',$response);        
        $responseArray      = self::xml2Array($response);        
                
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
    }
    
    
    function send($xml,$topic='Items',$addToUrl=''){        
        $ch = self::curlConnect();
                
        $url = self::$baseurl."/docs/XMLUpload.aspx?Topic=".$topic."&PartnerKey=".self::$partnerkey."&UserName=".self::$username."&Password=".self::$password.$addToUrl;       
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, utf8_encode($xml));
        $result = curl_exec($ch);                
        curl_close($ch);        
        
        return $result;
    }   
        
    
    
    /**
     * ExactProduct::getByEan()
     * Get's the full product xml message from Exact online. 
     * @param mixed $ean
     * @return
     */
    public static function getByEan($ean){    
        $ch = self::curlConnect();
        
        $url = self::$baseurl."/docs/XMLDownload.aspx?Topic=Items&Params_Code=".$ean."&PartnerKey=".self::$partnerkey."&UserName=".self::$username."&Password=".self::$password;        
        curl_setopt($ch, CURLOPT_URL, $url);        
        $result = curl_exec($ch);
        #echo $result;          
        #echo "result=$result";
        /* Finally close as we're finished with this session */
        #self::logTransaction($productId,'out','exactproduct__getbyean');
                
        curl_close($ch);
        $xmlParsed =  simplexml_load_string($result);
        #print_r($xmlParsed);
        return $xmlParsed; 
    }
}