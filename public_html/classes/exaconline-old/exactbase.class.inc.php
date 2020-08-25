<?php
class ExactBase {
    static $baseurl        = "https://start.exactonline.nl";
    static $username       = "afasting";
    static $password       = "Honk4bal!";
    // Test omgeving
    #static $partnerkey     = "959fdebc-e1bf-4c31-ac99-8acb28f0d7d7"; /* If you have one, the partnerkey with or without curly braces */
    // Productie omeving
    static $partnerkey     = "{959fdebc-e1bf-4c31-ac99-8acb28f0d7d7}"; /* If you have one, the partnerkey with or without curly braces */    
    	
   // static $division       = "273526";  /* Check the result of the first division retrieving for the division codes */
    
    static $division       = "325848";
    
    static $cookiefile     = "classes/exactonline/cookie.txt";
    static $crtbundlefile  = "classes/exactonline/cacert.pem"; /* this can be downloaded from http://curl.haxx.se/docs/caextract.html */

    public static function curlConnect(){
        /* Logging in */
        $header[1] = "Cache-Control: private";
        $header[2] = "Connection: Keep-Alive";
        
        /* init, don't term until you're completely done with this session */
        $ch = curl_init();
        
        /* Set all options */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, self::$cookiefile);
        curl_setopt($ch, CURLOPT_CAINFO, self::$crtbundlefile);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array("_UserName_"=>self::$username, "_Password_"=>self::$password));
        return $ch;        
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
    static function xml2Array($xml){
        return json_decode(json_encode((array) simplexml_load_string($xml)), 1);     
    }
    // Niet static maken.
    public function send($xml,$topic='Items'){        
		echo "sending...\n";
        $ch = self::curlConnect();
                
        $url = self::$baseurl."/docs/XMLUpload.aspx?Topic=".$topic."&PartnerKey=".self::$partnerkey."&UserName=".self::$username."&Password=".self::$password;
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, utf8_encode($xml));
        $result = curl_exec($ch);                
        curl_close($ch);
        if(!isset($_SERVER['HTTP_HOST'])){
		  #echo $xml;
		  #echo $result;
        }
        if($_SERVER['PHP_SELF'] == 'single-product-to-exact.php'){
            #echo $xml;
            #echo "\n--------------\n";
            #echo $result;    
        }
                        
        $resultArray =  self::xml2Array($result);        
		
		#print_r($resultArray);
		       
        foreach($resultArray['Messages']['Message'] as $message){      
            if(!is_array($message) || !isset($message['Topic'])){
				echo "\nGAAT NIET DOOR";				
				echo "\n$message";
                continue;
            }
            #echo "YEEEEEEY";
			
			
            $eanCode = $message['Topic']['Data']['@attributes']['keyAlt'];
            #echo "ean ".$eanCode.PHP_EOL;
            if(!$eanCode){                
                pre_r($message);
            }
            $status  = $message['Description'];
            
             // 0 error , 1=warning, 2=succes, 3=fatal
			
            if($message['@attributes']['type']!=2){                     
				echo "NOK\n";			
                $failedProduct = ProductDao::getBy('ean',$eanCode,true);
                
                ProductDao::setVal('exact_last_sync_fail',1,$failedProduct['id']);
                                
                $msg[] = "Error product id $productId";
                $msg[] = "Error melding \"".$responseArray['Messages']['Message']['Description'].'"';     
                $msg[] = "REPONSE:".print_r($resultArray,true);
                $msg[] = "---------------------------------------------------";
                $msg[] = "XML:";
                $msg[] = $xml;                                                             
                $productId = $failedProduct['id'];
                self::logTransaction($productId,'in','failed xml update');
                echo "$productId - $status this has been reported to ".Cfg::getPref('exact_online_status_mails').PHP_EOL;                                                
                #pre_r($message);                  
            }else{
				$successProduct = ProductDao::getBy('ean',$eanCode,true);
				echo "OK ean:".$eanCode.", nuicart:".$successProduct['id']."\n";											
                
                
				
				self::logTransaction($productId,'in','succesfull xml update');
                ProductDao::setVal('exact_last_sync_fail',0,$successProduct['id']);
				ProductDao::setVal('exact_synced',1,$successProduct['id']);
                $productId = $successProduct['id'];
            }
            ProductDao::setVal('exact_last_sync_desc ',$status,$productId);
                                   
        }
        if(is_array($msg) && $productId){
          //  mail(Cfg::getPref('exact_online_status_mails'),'Allamericansports product '.$productId.' kon niet worden bijgewerkt in Exact',join(PHP_EOL,$msg));
        }                    
        return $result;
    }    
    public static function logTransaction($productId,$inout,$xml){
        $uid     = $inout=='in'?'':User::getId();
        $sql     = sprintf('INSERT INTO exact_product_log 
                            (`fk_catalogue`,`fk_user`,`inout`,`date`,`xml`)
                            VALUE
                            (%d,%d,"%s",NOW(),\'%s\')',
                            $productId,$uid,$inout,quote($xml));         
		#echo $sql;
        #mail('anton@nui-boutkam.nl','test',$sql);
		query($sql,__METHOD__);
        #echo $sql;
                       
    }    
}
