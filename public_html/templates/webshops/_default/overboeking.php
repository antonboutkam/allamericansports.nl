<?php
class Overboeking{
    public static function run($params){    
        $params['tracking_code']        = Analytics::getTrackingCode($params['payorder']);
                                      
        Shoppingbasket::clear();
        if(OrderDao::isRelationOrder($params['payorder'],RelationDao::getMemberId())){
            $params['order']                = OrderDao::getOrder($params['payorder']);                
            $params['billing_info']         = nl2br(Cfg::getPref('billing_thankyouorder'));                
            $params['order']['total_price'] = str_replace('.',',',self::getTotals($params)); 
        }        
        $params['page']         = Webshopcms::getPageByTag($params['current_webshop_id'],"landingpage_overboeking_".$params['lang'],false);
        $vars                   = array('order_date','paymethod_visible','company_or_person','paydate_visible','cp_firstname','cp_lastname','order_id_vis','total_price');        
        foreach($vars as $varName)
            $params['page']['content'] = str_replace('['.$varName.']',$params['order'][$varName],$params['page']['content']);    
          
                  
        $params                 = Webshop::doFirst($params);        
        $params['content']      = parse('overboeking',$params);        
        return $params;
    }
    private static function getTotals($params){
        $params['return_totals_no_pdf_inc_vat'] = true;                
        $params['orderid'] = $params['payorder'];
        return Billgen::run($params);        
    }
}
