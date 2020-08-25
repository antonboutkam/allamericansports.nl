<?php
class Ogone{
    private $baseUrl        = 'https://secure.ogone.com/ncol/prod/orderstandard.asp';
    private $merchantid     = 'AllAmerican'; 		//PSPID
    private $sha_in         = 'aceview12!american'; //PSPID
    private $sha_out        = 'aceview12!american'; //PSPID
    
    
    public function getBanks(){
        // Niet van toepassing bij IDeal Easy van ABN.
        return;
    }    
    public function checkPayment($trxId,$shopId=''){
        // Niet mogelijk bij IDeal Easy van ABN
        Log::message('ideal_abnamroidealeasy_checkPayment',"niet mogelijk bij Ideal Easy van ABN\n",__METHOD__);                    
        return false;
    }
    public function getRedirectUrl($payorder,$relation_id,$lang='nl'){        
        $locale                 = $lang='nl'?'nl_NL':'en_US';
        $paymethod              = Paymethod::getById(ShoppingbasketDb::getPaymethod($payorder));        
        $order                  = OrderDao::getOrder($payorder);        
        $tmp                    = $this->getPaymethodAndBrand($paymethod['name']);
        $pm                     = $tmp['pm'];
        $brand                  = $tmp['brand'];                        
        $translation            = Translate::getTranslation();                
        $pspid                  = $this->merchantid;
		$shakey                 = $this->sha_in;
        $amount                 = ShoppingbasketDb::getTotal($payorder,true);;		
        $currency               = 'EUR';                
        $com                    = $translation['trans_yourorderat'].' '.$order['hostname'];                
        $cn                     = $order['company_or_person'];
        $homeurl                = 'http://'.$order['hostname'].'/'.$lang.'/';
        $catalogurl             = 'http://'.$order['hostname'].'/'.$lang.'/';
        $accepturl              = 'http://'.$order['hostname'].'/'.$lang.'/'.$translation['transl_thanks'].'.html';
        $declineurl             = 'http://'.$order['hostname'].'/'.$lang.'/checkout_paymethod.html?fail=1';
        $exceptionurl           = 'http://'.$order['hostname'].'/'.$lang.'/'.$translation['transl_pending'];
        $cancelurl              = 'http://'.$order['hostname'].'/'.$lang.'/checkout_paymethod.html?fail=1';
        $orderid                = $order['order_id'];
        $emailaddress           = $order['email'];
        $billing_address        = $order['billing_street'].' '.$order['billing_number'];
        $billing_country        = $order['billing_country'];
        $billing_city           = $order['billing_city'];
        $billing_postal_code    = $order['billing_postal'];
        $phonenumber            = $order['phone']; 
     
        /*
         * Verificatie
         */
        $shasource="";
        $shasource.="ACCEPTURL=".$accepturl.$shakey;
        $shasource.="AMOUNT=".$amount.$shakey;
        if ($brand<>'') $shasource.="BRAND=".$brand.$shakey;
        $shasource.="CANCELURL=".$cancelurl.$shakey;
        $shasource.="CATALOGURL=".$catalogurl.$shakey;
        $shasource.="CN=".$cn.$shakey;
        $shasource.="COM=".$com.$shakey;
        $shasource.="CURRENCY=".$currency.$shakey;
        $shasource.="DECLINEURL=".$declineurl.$shakey;
        $shasource.="EMAIL=".$emailaddress.$shakey;
        $shasource.="EXCEPTIONURL=".$exceptionurl.$shakey;
        $shasource.="HOMEURL=".$homeurl.$shakey;
        #$shasource.="LANGUAGE=".$locale.$shakey;
        $shasource.="ORDERID=".$orderid.$shakey;
        $shasource.="OWNERADDRESS=".$billing_address.$shakey;
        $shasource.="OWNERCTY=".$billing_country.$shakey;
        $shasource.="OWNERTELNO=".$phonenumber.$shakey;
        $shasource.="OWNERTOWN=".$billing_city.$shakey;
        $shasource.="OWNERZIP=".$billing_postal_code.$shakey;
        if ($pm<>'') $shasource.="PM=".$pm.$shakey;
			$shasource.="PSPID=".$pspid.$this->sha_in;

		#exit($shasource);
		
        //print $shasource."<br /><br />";
        $shasign = strtoupper(sha1($shasource));
		#exit($shasign);
        //print $shasign;
		

						
        $props['post_data'] = array (
                'ACCEPTURL'             => $accepturl,
                'AMOUNT' 		=> $amount,
                'BRAND' 		=> $brand,
                'CANCELURL'             => $cancelurl,
                'CATALOGURL'            => $catalogurl,
                'CN' 			=> $cn,
                'COM' 			=> $com,
                'CURRENCY' 		=> $currency,
                'DECLINEURL'            => $declineurl,
                'EMAIL' 		=> $emailaddress,
                'EXCEPTIONURL'          => $exceptionurl,
                'HOMEURL' 		=> $homeurl,
               /* 'LANGUAGE' 		=> $language,*/
                'ORDERID' 		=> $orderid,
                'OWNERADDRESS'          => $billing_address,
                'OWNERCTY' 		=> $billing_country,
                'OWNERTELNO'            => $phonenumber,
                'OWNERTOWN'             => $billing_city,
                'OWNERZIP' 		=> $billing_postal_code,
                'PM' 			=> $pm,
                'PSPID' 		=> $pspid,
                'SHASIGN' 		=> $shasign,
                'SUBMIT2' 		=> ''
        );        
                    
        Log::message('ogone_getRedirectUrl',"Redirect naar ogon voor order ".$orderid."\n",__METHOD__);
        $props['post_url']              = $this->baseUrl;
        $props['post_form']             = $this->makeForm($props); 
        
        return $props;                 
    } 
    private function getPaymethodAndBrand($paymethod){
        switch (strtolower($paymethod))
        {
                case 'paypal':
                        $pm = 'PAYPAL';
                        $brand = '';
                break;
                case 'visa':
                        $pm = 'CreditCard';		
                        $brand = 'VISA';	
                break;
                case 'mastercard':
                        $pm = 'CreditCard';			
                        $brand = 'MasterCard';
                break;                          
                case 'webshop giftcard':
                        $pm = 'InterSolve';
                        $brand = 'Webshopgiftcard';
                break;
                default:
                        $pm = 'iDEAL';
                        $brand = 'iDEAL';
                break;            
        } 
        
        return array('pm'=>$pm,'brand'=>$brand);
    }
    
    
    private function makeForm($props){        
        $out[] = '<!DOCTYPE html>';
        $out[] = '<html>';
        $out[] = '<head><title>Redirecting to PSP</title></head>';
        $out[] = '<body>';
        $out[] = sprintf('<form id="autosubmit_form" method="post" action="%s">',$props['post_url']);
        foreach($props['post_data'] as $field=>$value){
            $out[] = sprintf('<input type="hidden" name="%s" value="%s">',$field,$value);     
        }
        $out[] = '</form>';
        $out[] = '<script type="text/javascript">';                
        $out[] = ' document.getElementById("autosubmit_form").submit();';
        $out[] = '</script>';
        $out[] = '</body>';
        $out[] = '</html>';
        return join(PHP_EOL,$out);        
    }    

}