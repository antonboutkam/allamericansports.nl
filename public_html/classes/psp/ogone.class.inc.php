<?php

/**
 * Ogone
 * 
 * Deze klasse is gemaakt voor de Basis Ogone integratie methode waarbij de gebruiker naar een tussenpagina van Ogone wordt gestuurd.
 * Om de invoer van CreditCard informatie te integreren in de checkout pagina van de site zelf moet de Alias variant gebruikt worden: ogonealiasgw.class.inc.php 
 * 
 * 
 * @author Anton Boutkam
 * @copyright 2013
 * @version $Id$
 * @access public
 */
class Ogone{
    private $baseUrl;
    private $merchantid;
    private $sha_in;
    private $sha_out;
    
    public function __construct(){        
        if(Cfg::get('PSP_TEST_MODE')){
            $this->baseUrl      = 'https://secure.ogone.com/ncol/test/orderstandard.asp';
            $this->merchantid   = 'NuiCartOgoneTest';
            $this->sha_in       = '123x32o834D234d33423d';
            $this->sha_out      = 'x213ed3asefswdDddeds323';
        }else{
            /*   
            $this->baseUrl      = 'https://secure.ogone.com/ncol/test/orderstandard.asp';
            $this->merchantid   = 'NuiCartOgoneTest';
            $this->sha_in       = '123x32o834D234d33423d';
            $this->sha_out      = 'x213ed3asefswdDddeds323';
            */         
            $this->baseUrl      = 'https://secure.ogone.com/ncol/prod/orderstandard.asp';
            $this->merchantid   = 'AllAmerican';            
            $this->sha_in       = 'aceview12!american';
            $this->sha_out      = 'aceview12!american';
            
        }               
    }
    
    public function getBanks(){
        // Niet van toepassing.
        return;
    }    
    public function checkPayment($trxId='niet van toepassing',$shopId='niet van toepassing'){
        
        $temp = parse_url($_SERVER['REQUEST_URI']);
        
        parse_str($temp['query'],$array);
        
        $shasign = $array['SHASIGN'];
        unset($array['SHASIGN']);        
        #ksort($array,SORT_NATURAL);
        #uksort($array, 'strcasecmp');
        
        $shastring="";

        $validParamNames = array( 
            'AAVADDRESS', 'AAVCHECK', 'AAVZIP', 'ACCEPTANCE', 'ALIAS', 'AMOUNT', 'BIN', 'BRAND', 'CARDNO', 'CCCTY', 'CN', 'COMPLUS', 'CREATION_STATUS', 'CURRENCY', 'CVCCHECK', 'DCC_COMMPERCENTAGE', 'DCC_CONVAMOUNT', 'DCC_CONVCCY', 'DCC_EXCHRATE', 'DCC_EXCHRATESOURCE',
            'DCC_EXCHRATETS','DCC_INDICATOR','DCC_MARGINPERCENTAGE','DCC_VALIDHOURS','DIGESTCARDNO', 'ECI', 'ED', 'ENCCARDNO', 'IP', 'IPCTY', 'NBREMAILUSAGE', 'NBRIPUSAGE', 'NBRIPUSAGE_ALLTX',
            'NBRUSAGE', 'NCERROR', 'ORDERID','PAYID', 'PM', 'SCO_CATEGORY', 'SCORING', 'STATUS', 'SUBBRAND', 'SUBSCRIPTION_ID', 'TRXDATE', 'VC'
        );        
        
        foreach($array as $id => $item){        
            $uId = strtoupper($id);            
            if(in_array($uId,$validParamNames) && trim($item)!=''){                
                $arraySha[] =$uId."=$item".$this->sha_out;
            }            
        }
        
        asort($arraySha);
        $shastring          = implode('', $arraySha);
        $shasigned_remote   = sha1($shastring);
        
        
        if(strtoupper($shasign) == strtoupper($shasigned_remote)){
            // Het bericht is valide / echt
            if($array['STATUS']==9){
                return true;
            }            
        }
        // Niet mogelijk bij IDeal Easy van ABN
        #Log::message('ideal_abnamroidealeasy_checkPayment',"niet mogelijk bij Ideal Easy van ABN\n",__METHOD__);                    
        return false;
    }
    public function getRedirectUrl($payorder,$relation_id,$lang='nl'){     
        
        $locale                 = $lang=='nl'?'nl_NL':'en_US';
        $paymethod              = Paymethod::getById(ShoppingbasketDb::getPaymethod($payorder));        
        $order                  = OrderDao::getOrder($payorder);        
        $tmp                    = $this->getPaymethodAndBrand($paymethod['name']);
        $pm                     = $tmp['pm'];
        $brand                  = $tmp['brand'];                        
        $translation            = Translate::getTranslation();                
        $pspid                  = $this->merchantid;
		$shakey                 = $this->sha_in;
        
#        $tmp['orderid']                         =  $payorder;
#        $tmp['return_totals_no_pdf_inc_vat']    = true;        
#        $amount                                 = str_replace('.','',str_replace(',','',Billgen::run($tmp)));
       
        $amount                 = str_replace(',','',ShoppingbasketDb::getTotal($payorder,false));

        
        		
        $currency               = 'EUR';                
        $com                    = 'Webshop order';                
        $cn                     = $order['company_or_person'];
        if($_SERVER['IS_DEVEL']){
            $order['hostname']      = 'allamericansports.nuidev.nl';    
        }else{
            $order['hostname']      = 'allamericansports.nl';
        }
        
        $homeurl                = 'http://'.$order['hostname'].'/'.$lang.'/';
        $catalogurl             = 'http://'.$order['hostname'].'/'.$lang.'/';
        $accepturl              = 'http://'.$order['hostname'].'/'.$lang.'/paymentok.html';
        $declineurl             = 'http://'.$order['hostname'].'/'.$lang.'/checkout_paymethod.html?fail=1';
        $exceptionurl           = 'http://'.$order['hostname'].'/'.$lang.'/'.$translation['transl_pending'];
        $cancelurl              = 'http://'.$order['hostname'].'/'.$lang.'/checkout_paymethod.html?fail=1';
        $orderid                = $order['order_id'];
        $emailaddress           = $order['email'];
        $billing_address        = trim($order['billing_street'].' '.$order['billing_number']);
        $billing_country        = trim($order['billing_country']);
        $billing_city           = trim($order['billing_city']);
        $billing_postal_code    = trim($order['billing_postal']);
        $phonenumber            = trim($order['phone']); 
     
        /**
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
        
        if(trim($billing_country)!='')
            $shasource.="OWNERCTY=".$billing_country.$shakey;
        
        if(trim($phonenumber)!='')
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
                'ACCEPTURL'     => $accepturl,
                'AMOUNT' 		=> $amount,
                'BRAND' 		=> $brand,
                'CANCELURL'     => $cancelurl,
                'CATALOGURL'    => $catalogurl,
                'CN' 			=> $cn,
                'COM' 			=> $com,
                'CURRENCY' 		=> $currency,
                'DECLINEURL'    => $declineurl,
                'EMAIL' 		=> $emailaddress,
                'EXCEPTIONURL'  => $exceptionurl,
                'HOMEURL' 		=> $homeurl,
               /* 'LANGUAGE' 		=> $language,*/
                'ORDERID' 		=> $orderid,
                'OWNERADDRESS'  => $billing_address,
                'OWNERCTY' 		=> $billing_country,
                'OWNERTELNO'    => $phonenumber,
                'OWNERTOWN'     => $billing_city,
                'OWNERZIP' 		=> $billing_postal_code,
                'PM' 			=> $pm,
                'PSPID' 		=> $pspid,
                'SHASIGN' 		=> $shasign,
                'SUBMIT2' 		=> ''
        );        
                    
        $message = "Order: ".$orderid.", amount ".$amount.", brand:".$brand.", shasign:".$shasign;                    
        Log::message('ogone_getRedirectUrl',$message,__METHOD__);
        
        $props['post_url']              = $this->baseUrl;
        $props['post_form']             = $this->makeForm($props); 
        
        return $props;                 
    } 
    private function getPaymethodAndBrand($paymethod){

        switch (strtolower($paymethod))
        {
                case 'sport en fit cadeaukaart':
                    $pm = 'Intersolve';
                    $brand = 'Sport & Fit Cadeau';
                break;
                case 'paypal':
                        $pm = 'PAYPAL';
                        $brand = '';
                break;
                case 'visa':
                        $pm = 'CreditCard';		
                        $brand = 'VISA';	
                break;
                case 'maestro':
                        $pm = 'CreditCard';			
                        $brand = 'MaestroUK';
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
        #$out[] = '<input type="submit">';
        $out[] = '</form>';
        
        $out[] = '<script type="text/javascript">';                
        $out[] = ' document.getElementById("autosubmit_form").submit();';
        $out[] = '</script>';
        $out[] = '</body>';
        $out[] = '</html>';
        return join(PHP_EOL,$out);        
    }    

}
