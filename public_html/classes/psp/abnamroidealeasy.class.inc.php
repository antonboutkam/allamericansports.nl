<?php
class Abnamroidealeasy{
    private $baseUrl        = 'https://internetkassa.abnamro.nl/ncol/prod/orderstandard.asp';
    private $merchantid     = 'vervoortliv'; //PSPID
    
    
    public function getBanks(){
        // Niet van toepassing bij IDeal Easy van ABN.
        return;
    }    
    public function checkPayment($trxId,$shopId=''){
        // Niet mogelijk bij IDeal Easy van ABN
        Log::message('ideal_abnamroidealeasy_checkPayment',"niet mogelijk bij Ideal Easy van ABN\n",__METHOD__);                    
        return false;
    }
    public function getRedirectUrl($payorder,$relation_id){
        #echo "order id is ".$payorder."<br>";
        #echo "payumethod is ".ShoppingbasketDb::getPaymethod($payorder)."<br>";
        #exit();
        #$paymethod              = Paymethod::getById(ShoppingbasketDb::getPaymethod($payorder));
        #echo "PAYMETHOD IS $paymethod";
        
        $props['post_url']              = $this->baseUrl;
        $props['post_data']['PSPID']    = $this->merchantid;
        $props['post_data']['orderID']  = $payorder;
        $props['post_data']['amount']   = ShoppingbasketDb::getTotal($payorder,true);
        $props['post_data']['currency'] = 'EUR';
        $props['post_data']['language'] = 'NL_NL';
        $props['post_data']['COM']      = "Uw aankoop bij ".$_SERVER['HTTP_HOST'];
        $props['post_data']['PM']       = 'iDEAL';
                
        Log::message('ideal_abnamroidealeasy_checkPayment',"niet mogelijk bij Ideal Easy van ABN\n"."\n",__METHOD__);
        Log::message('ideal_abnamroidealeasy_checkPayment',json_encode($props['post_data'])."\n",__METHOD__);
        $props['post_form']             = $this->makeForm($props); 
        
        return $props;                 
    } 
    private function makeForm($props){        
        $out[] = sprintf('<form id="autosubmit_form" method="post" action="%s">',$props['post_url']);
        foreach($props['post_data'] as $field=>$value){
            $out[] = sprintf('<input type="hidden" name="%s" value="%s">',$field,$value);     
        }
        $out[] = '</form>';
        $out[] = '<script type="text/javascript">';                
        $out[] = ' document.getElementById("autosubmit_form").submit();';
        $out[] = '</script>';
        return join(PHP_EOL,$out);        
    }    
}