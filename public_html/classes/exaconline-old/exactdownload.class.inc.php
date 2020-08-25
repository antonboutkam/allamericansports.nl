<?php
class ExactDownload extends ExactBase{

    
    function batchStock(){
        $ch = self::curlConnect();
        
        $url = self::$baseurl."/docs/XMLDownload.aspx?Topic=StockPositions&pagesize=5&PartnerKey=".self::$partnerkey."&UserName=".self::$username."&Password=".self::$password;        
        curl_setopt($ch, CURLOPT_URL, $url);        
        $result = curl_exec($ch);        
        #echo "result=$result";
        /* Finally close as we're finished with this session */
        curl_close($ch);
        $xmlParsed =  simplexml_load_string($result);
        
        print_r((array)$xmlParsed);
        print_r((array)$xmlParsed->Topics->Topic['ts_d']);
        
        // Mogelijk werkt dit niet goed als een product meerdere voorraden heeft op meerdere locaties in het magazijn.        
        #return (int)$xmlParsed->StockPositions->StockPosition->CurrentQuantity;                
        #echo "<pre>".htmlentities($result)."</pre>";                
        #echo $result;
        #return $result;
    }    
    
    
}