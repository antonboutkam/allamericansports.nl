<?php
class MyAccount{
    public function run($params){

        if(isset($params['_do']) && !in_array($params['_do'], array('updateaccount', 'changepass'))){
            throw new Exception("Invalid argument _do");
        }

        if(isset($params['contact'])){
            $params['contact'] = $this->sanitize($params['contact']);
        }
        if($params['request_uri'] == '/myaccount.html'){
            redirect('/nl/myaccount.html');
        }
        
        #$params['view']             = 'wide';  
        $params                     = Webshop::doFirst($params);
        $params['country_iso']      = Countryiso::getAll();
				
        if(!RelationDao::isMember())
            redirect($params['root'].'/login.html');
        if($params['_do']=='updateaccount'){
            parse_str($params['data'],$data);
            $data['contact'] = $this->sanitize($data['contact']);
            RelationDao::store($data['contact'], $_SESSION['relation']['id']);
            $_SESSION['relation']   = RelationDao::getById($_SESSION['relation']['id']);
        }
        if($params['_do'] == 'changepass'){
            $update['password'] = $params['password'];
            RelationDao::store($update, $_SESSION['relation']['id']);
        }

        $params['relation']         = $this->sanitize($_SESSION['relation']);
        $params['cart_items']       = Shoppingbasket::getTotalQuantity();
        $params['basket']           = Shoppingbasket::getBasket();
        $paymethods                 = Paymethod::getAll('name');
        $params['paymethods']       = $paymethods['data'];
        $params['types']            = ProductTypeDao::getWebshopProductTypes($params['hostname'],true);               
        $params['total_quantity']   = Shoppingbasket::getTotalQuantity();

        $where[]                    = sprintf('o.relation_id = %d',$params['relation']['id']);
        $params['orders']           = OrderDao::find($where);
        
        $params['orders']['rowcount'] = $params['orders']['rowcount']+count($params['old_bills']);
        if($params['orders']['rowcount']>0)
            $params['hasbills'] = 1;
        $params['content']  = parse('myaccount',$params);
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
