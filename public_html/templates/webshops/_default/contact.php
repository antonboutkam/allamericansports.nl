<?php
class Contact{
    public function run($params){
        $params                     = Webshop::doFirst($params);

        if(isset($params['_do']) && $params['_do'] != 'sentmail'){
            throw new Exception("Invalid argument detected _do");
        }
        $aUnSanitised = array(
            'email' => $params['email'],
            'hostname' => $params['hostname'],
            'subject' => $params['subject'],
            'name' => $params['name'],
            'phone' => $params['phone']
        );
        $aSanitised = $this->sanitize($aUnSanitised);
        $params = array_merge($params, $aSanitised);


        if($params['_do']=='sentmail'){
            $params['mailsend'] = 'true';
            if($params['secret_code'] == 65)
                Mailer::sendContactEmail($aSanitised['email'], $aSanitised['hostname'], $aSanitised['subject'], $aSanitised['name'], $aSanitised['phone']);
            else
                exit('Spammer detected ');            
        }

        $params['relation']                     = $_SESSION['relation'];
        $params['relation'] = $this->sanitize($params['relation']);

        $params['settings']                     = Cfg::getPrefs();
        $params['webshop_companyname']          = Webshop::getWebshopSetting($params['hostname'],'company_name');
        $params['settings']['billing_address']  = nl2br($params['settings']['billing_address']);
        $params['settings']['billing_address']  = str_replace('@','#at#',$params['settings']['billing_address']);        
        #$params['cart_items']                  = Shoppingbasket::getTotalQuantity();
        #$params['types']                       = ProductTypeDao::getWebshopProductTypes($params['hostname'],true);
        $params['content']                      = parse('contact',$params);
        return $params;
    }

    function sanitize($aData){

        $aOut = array();
        if(!empty($aData)){
            foreach($aData as $sFieldName => $sFieldValue){

                if($sFieldName == 'email'){

                    $sFieldValue = str_replace('@', 'atatatsign', $sFieldValue);
                    $sFieldValue = preg_replace('/[^a-zA-Z0-9\._ -]+/i', '', $sFieldValue);

                    $aOut[$sFieldName] = str_replace('atatatsign', '@', $sFieldValue);

                }else{

                    $aOut[$sFieldName] = preg_replace('/[^a-zA-Z0-9\._ -]+/i', '', $sFieldValue);
                }

            }
        }
        return $aOut;

    }
}
