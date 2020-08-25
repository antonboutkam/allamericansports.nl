<?php
class GlsTransaction {
	public static function NPDInternetChecksum ($cData)
        {
                $nPos = '';
                $nChk = '';
                $nAsc = '';

                for ($i = 0; $i < strlen($cData); $i++)
                {
                        // asci waarde bepalen
                        $nAsc = ord(substr($cData, $i, 1));
                        if ($nAsc >= 65 && $nAsc <= 90)
                        $nAsc = $nAsc - 64;
                        elseif ($nAsc >= 48 && $nAsc <= 57)
                        $nAsc = $nAsc - 21;
                        $nChk = $nChk + (($i + 1) * $nAsc);
                }
        	return $nChk;
        }

    public static function glsAdd($ordernumber){
        $order = OrderDao::getOrder($ordernumber);            
		$url = 'http://86.81.14.7:2746/gls_db.php';
                
        $address    = $order['shipping_street'].' '.$order['shipping_number'];
        $postal     = $order['shipping_postal'];
        $number     = $order['shipping_number'];
        $city       = $order['shipping_city'];
        $country    = $order['shipping_country'];
        if(trim($order['shipping_street'])==''){
            $address    = $order['billing_street'];
            $postal     = $order['billing_postal'];
            $number     = $order['billing_number'];
            $city       = $order['billing_city'];
            $country    = $order['billing_country'];
        }
        $remboursbedrag = 0;
        if(strtolower($order['paymethod_visible'])=='rembours'){
            $requered_bill_props =array('orderid'=>$order['order_id'],'hostname'=>$order['hostname'],'return_totals_no_pdf'=>true); 
            $remboursbedrag = Billgen::run($requered_bill_props);                                                              
        } 
        
        $data = array(
            "bedrijfsnaam"      => $order['company_name'],
            "naam"              => $order['cp_firstname']." ".$order['cp_lastname'],
            "adres"             => $address,
            "postcode"          => $postal,
            "huisnr"            => $number,        
            "huisnrtoev"        => '',
            "plaats"            => $city,
            "land"              => $country,
            "ref"               => $order['order_id_vis']
        );
        
		if($remboursbedrag)
			$data['remboursbedrag'] =  (float)$remboursbedrag + (((float)$remboursbedrag * (float)Cfg::getPref('btw'))/100);		
	    $postfields = http_build_query($data);
        
//		print_r($params);
        
		$user_agent = "Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)";
		$ch = curl_init();
		// curl_setopt($ch, CURLOPT_POST,1);
		// curl_setopt($ch, CURLOPT_POSTFIELDS,$postfields);
		curl_setopt($ch, CURLOPT_URL,$url.'?'.$postfields);
		curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        
		if(!$_SERVER['IS_DEVEL'])
            $result=curl_exec ($ch);
		curl_close ($ch);
        return array('postfields'=>'','url'=>$url.'?'.$postfields,'remboursbedrag'=>$remboursbedrag);    
    }
	public static function getTrackinglink($VREF){
	
			$cNPDVerladerNummer = "89240001";
        	$cUwOrderNummer = $VREF;
	        $nUwEncryptieCode = "984";

        	$nControleGetal = self::NPDInternetChecksum ($cNPDVerladerNummer . $cUwOrderNummer);

	        #om een of andere reden niet nodig
        	$nControleGetal += $nUwEncryptieCode;

	        $cURL = "http://services.gls-netherlands.com/tracking/ttlink.asp?";
        	$cURL .= "NVRL=" . $cNPDVerladerNummer ;
	        $cURL .= "&VREF=" . $cUwOrderNummer;
        	$cURL .= "&CHK=" . $nControleGetal;
	        return $cURL;
	}

}
