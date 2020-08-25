<?php
class Multisafepay{

    private $sApiUrl = null;
    private $sApiKey     = null;

    function __construct()
    {
        $this->sApiKey = Cfg::get('MULTISAFEPAY_API_KEY');

        /*
        if(isset($_SERVER['IS_DEVEL']))
        {
            $this->sApiUrl = 'https://testapi.multisafepay.com/v1/json/';
        }
        else
        {
        */
            $this->sApiUrl = 'https://api.multisafepay.com/v1/json/';
        /*
        }
        */
    }
    public function getPaymentMethods($sCountryISO3166, $fAmountInCents)
    {
        $sUrl = "gateways?country=$sCountryISO3166&currency=EUR&amount=$fAmountInCents";
        $sData = $this->callUrl($sUrl);
        $aData = json_decode($sData, true);
        return $aData;
    }
    public function getBanks($iGatewayId){

        $sUrl = "issuers/$iGatewayId";
        $sData = $this->callUrl($sUrl);
        $aData = json_decode($sData, true);
        return $aData;
    }
    function checkPayment($iOrderId)
    {
        $sData = $this->callUrl('orders/'.$iOrderId);
        $aData = json_decode($sData, true);


        $iTotalPrice = ShoppingbasketDb::getTotal($iOrderId,true);

        if($iTotalPrice != $aData['data']['amount'])
        {
            return false;
        }
        if(strtolower($aData['data']['status']) != 'completed')
        {
            return false;
        }

        // Er is betaald en het bedrag klopt.
        Log::message('ideal_multisafepay_checkPayment','orders/'.$iOrderId."\n",__METHOD__);
        Log::message('ideal_multisafepay_checkPayment',$sData."\n\n",__METHOD__);

        // Dus..
        return true;
    }
    public function getRedirectUrl($iOrderId, $iRelationId)
    {
        $aPaymethod = Paymethod::getById(ShoppingbasketDb::getPaymethod($iOrderId));
        $sGateway = null;


        $aMap = array(
            "IDEAL" => "IDEAL",
            "Overboeking" => "BANKTRANS",
            "MultiSafepay" => "WALLET",
            "Visa" => "VISA",
            "Maestro" => "MAESTRO",
            "MasterCard" => "MASTERCARD",
        );

        if(isset($aPaymethod['name']) && isset($aMap[$aPaymethod['name']]))
        {
            $sGateway = $aMap[$aPaymethod['name']];
        }

        $aRelation = RelationDao::getById($iRelationId);
        if(isset($aRelation['fk_locale']) && $aRelation['fk_locale'] == 52)
        {
            $sLocale = 'en';
        }
        else
        {
            $sLocale = 'nl';
        }

        $sForwardedForIp = null;
        if(isset($_SERVER['X-FORWARED-FOR']))
        {
            $sForwardedForIp = $_SERVER['X-FORWARED-FOR'];
        }

        $sProto = 'http';
        if(isset($_SERVER['HTTPS'])){
            $sProto = 'https';
        }

        $sBase = $sProto.'://'.$_SERVER['HTTP_HOST'];

        $aOrder = array(
            'type' => "redirect",
            'order_id' => $iOrderId,
            'currency' => 'EUR',
            'amount' => ShoppingbasketDb::getTotal($iOrderId,true),
            'gateway' => $sGateway,
            "description" => 'Uw bestelling bij allamericansports.nl met ordernummer '.$iOrderId,
            "var1" => null,
            "var2" => null,
            "var3" => null,
            "items" => null,
            "manual" => null,
            "days_active" => null,
            "payment_options" => array(
                "notification_url" => $sBase.'/paypage.html',
                "redirect_url" => $sBase.'/paymentok.html',
                "cancel_url" => $sBase.'/checkout_paymethod.html',
                "close_window" => true
            ),
            "customer" => array(
                "locale" => $sLocale,
                "ip_address" => $_SERVER['REMOTE_ADDR'],
                "forwarded_ip" => $sForwardedForIp,
                "first_name" => $aRelation['cp_firstname'],
                "last_name" => $aRelation['cp_lastname'],
                "address1" => $aRelation['billing_street'],
                "address2" => null,
                "house_number" => $aRelation['billing_number'],
                "zip_code" => $aRelation['billing_postal'],
                "city" => $aRelation['billing_city'],
                "state" => null,
                "country" => $aRelation['billing_country'],
                "phone" => $aRelation['phone_mobile'],
                "email" => $aRelation['email'],
                "disable_send_email" => false,
                "user_agent" => $_SERVER['HTTP_USER_AGENT']
            ),
            "google_analytics" => array(
                "account" => "UA-1015096-3"
            )
        );

        $sData = $this->callUrl('orders', $aOrder);
        $aData = json_decode($sData, true);

        return $aData['data']['payment_url'];
        exit();
    }

    private function callUrl($sUrl, $aPostData = null)
    {
        $ch = curl_init();
        $aHeaders = array(
            "Accept: application/json",
            "api_key: ".$this->sApiKey
        );

        if($aPostData != null)
        {
            $aHeaders[] = "Content-Type: application/json";
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($aPostData));
        }

        curl_setopt($ch, CURLOPT_URL, $this->sApiUrl.$sUrl);



        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


        $sResponse = curl_exec($ch);
        curl_close($ch);
        return $sResponse;
    }
}
/*
$this->api_key = trim($api_key);
}


public function processAPIRequest($http_method, $api_method, $http_body = NULL) {
    if (empty($this->api_key)) {
        throw new \Exception("Please configure your MultiSafepay API Key.");
    }

    $url = $this->api_url . $api_method;
    $ch = curl_init($url);


    if ($http_body !== NULL) {
        $request_headers[] = "Content-Type: application/json";
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $http_body);
    }



    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $http_method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

    $body = curl_exec($ch);

    if($this->debug){
        $this->request = $http_body;
        $this->response = $body;
    }

    if (curl_errno($ch)) {
        throw new \Exception("Unable to communicatie with the MultiSafepay payment server (" . curl_errno($ch) . "): " . curl_error($ch) . ".");
    }

    curl_close($ch);
    return $body;
}
}
*/
