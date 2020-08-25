<?php
class Sisow{
    private $baseUrl        = 'www.sisow.nl';
    private $merchantid     = '2537469419';
    private $merchantKey    = '6ba45cdc5b8a1fd82159f3680d35c77411233d31';
    private $bankDesc       = 'Vangoolstoffen order';
    
    public function getBanks(){
        $testmode = Cfg::get('PSP_TEST_MODE')?'?test=true':'';
        $out      = array();
        $url      = sprintf('http://%s/Sisow/iDeal/RestHandler.ashx/DirectoryRequest%s',$this->baseUrl,$testmode);
        $data     = (array)simplexml_load_file($url);
        foreach($data['directory'] as $bank){ 
            $dat = array('id'=>(string)$bank->issuerid,'value'=>(string)$bank->issuername);
            $out[(string)$bank->issuerid] = $dat;            
        }
        return $out;
    }    
    public function checkPayment($trxId,$shopId=''){
        $props = array(
            'trxid'         => $trxId,
            'shopid'        => $shopId,
            'merchantid'    => $this->merchantid,
            'sha1'          => sha1($trxId.$shopId.$this->merchantid.$this->merchantKey));
        $restQuery          = http_build_query($props);
        $urlTpl             = 'https://%s/Sisow/iDeal/RestHandler.ashx/StatusRequest?%s';
        $url                = sprintf($urlTpl,$this->baseUrl,$restQuery);
        $xml                = file_get_contents($url);
        $data               = (array)simplexml_load_string($xml);    
        Log::message('ideal_sisow_checkPayment',$url."\n",__METHOD__);
        Log::message('ideal_sisow_checkPayment',$xml."\n\n",__METHOD__);   
        return $data;        
    }
    public function getRedirectUrl($payorder,$relation_id){
        #echo "order id is ".$payorder."<br>";
        #echo "payumethod is ".ShoppingbasketDb::getPaymethod($payorder)."<br>";
        #exit();
        $paymethod              = Paymethod::getById(ShoppingbasketDb::getPaymethod($payorder));
        #echo "PAYMETHOD IS $paymethod";
        $props['shopid']        = '';        
        $props['merchantid']    = $this->merchantid;                
        if($paymethod['name']=='Paypal'){
            $method = 'paypalec';
        }else
            $method = strtolower($paymethod['name']);      
        
        $props['payment']       = $method; //[mistercash|paypalec|ideal]
        $props['purchaseid']    = $payorder;
        $props['amount']        = ShoppingbasketDb::getTotal($payorder,true);    //Bedrag in centen
        #echo "amount is ".$props['amount'];
        if($props['payment']=='ideal'){
            $bank               =  Shoppingbasket::getBank();
            $props['issuerid']  = $bank['id']; // Id van de ideal bank
        }            
        $props['testmode']      = Cfg::get('PSP_TEST_MODE');
        $props['entrancecode']  = md5($payorder.'-'.$relation_id);// Unieke code die ook in de return url weer wordt gebruikt
        $props['description']   = sprintf('%s: %s',$this->bankDesc,$payorder);        
        $host                   = 'http://'.$_SERVER['HTTP_HOST'];
        $props['returnurl']     = sprintf('%s/paymentok.html',$host);
        $props['cancelurl']     = sprintf('%s/checkout_paymethod.html',$host);
        $props['callbackurl']   = sprintf('%s/paypage.html',$host);
        $props['sha1']          = sha1($props['purchaseid'].$props['entrancecode'].$props['amount'].$props['merchantid'].$this->merchantKey);           
        #pre_r($props);
        #exit();    
        $restQuery              = http_build_query($props);
        $urlTpl                 = 'http://%s/Sisow/iDeal/RestHandler.ashx/TransactionRequest?%s';
        #pre_r($props);
        #echo "<br><br>";
        #exit($urlTpl);
        $url                    = sprintf($urlTpl,$this->baseUrl,$restQuery);        
        $xml                    = file_get_contents($url);
        
        $data                   = (array)simplexml_load_string($xml);   
        #pre_r($data);
        if((string)$data['error']->errormessage == 'simulation forbidden'){  
            $msg = 'Ideal simulatie modus staat uit, het is niet mogelijk om een ideal transactie te simuleren';
            trigger_error($msg,E_USER_WARNING);            
        }
        
        Log::message('ideal_sisow_getRedirectUrl',$url."\n",__METHOD__);
        Log::message('ideal_sisow_getRedirectUrl',$xml."\n",__METHOD__);                
        if($data['transaction']->error){
            ShopError::notify('Payment service provider error',$data['transaction']->error);
            return $props['cancelurl'];
        }
        return urldecode($data['transaction']->issuerurl);                
    }     
}