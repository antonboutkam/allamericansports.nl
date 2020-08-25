<?php
class Mailer extends MimeMailer{
    static function sendContactEmail($fromEmail,$hostname,$subject,$name,$phone){
        #_d($params);
        #exit();
        
        
        $settings                       = Webshop::getWebshopSettings($hostname);
        $mail['company_name']           = $settings['company_name'];
        $mail['hostname']               = $hostname;
        if($_SERVER['IS_DEVEL']){
            $mail['hostname'] = $_SERVER['HTTP_HOST'];
        }else if(strpos(' '.$_SERVER['HTTP_HOST'],'nuicart.')){
            $mail['hostname'] = $_SERVER['HTTP_HOST'];
        }
        
        
        $mail['trans_withkindregards']  = 'Met vriendelijke groeten';
        $mail['ip']                     = $_SERVER['REMOTE_ADDR'];
        $mail['time']                   = date('l j F Y H:i');
        $mail['subject']                = $subject;
        $mail['name']                   = $name;
        $mail['phone']                  = $phone;                
        $mail['content']                = parse('mail/contact',$mail);
        $html                           = parse('mail/container',$mail,null,'webshops');
        
        #echo "van $fromEmail aan ".$settings['contact_email'];
        
        #self::sendMail($fromEmail,'info@nuicart.nl',$hostname,'Bericht via '.$hostname,$html);                                    
        self::sendMail($fromEmail,$settings['contact_email'],$hostname,'Bericht via '.$hostname,$html);
        
        #exit($html);      
    }
    static function sendPlainHtml($plainHtml,$toEmail,$fromEmail,$order){
       #_d($params);
                                        
        $params['hostname']             = baseHost($order['hostname']); 
        $params['company_name']         = Webshop::getWebshopSetting($params['hostname'],'company_name');
        $params['company_email']        = Webshop::getWebshopSetting($params['hostname'],'contact_email');        
        $params['company_phone']        = Webshop::getWebshopSetting($params['hostname'],'contact_phone');
        $params['mailing_color']        = Webshop::getWebshopSetting($params['hostname'],'mailing_color');
        $params['mailing_txt_color']    = Webshop::getWebshopSetting($params['hostname'],'mailing_txt_color');
        
        $lang                           = Lang::getCodeByLanguageId($order['fk_locale']);        
        $lang                           = empty($lang)?'nl':$lang;
        $translation                    = Translate::getTranslationNoInject($lang,'mail_orderpicked','webshops');        
        if(!empty($translation))
            $params                     = array_merge($params,$translation);

        
        $params['content']              = $plainHtml;
        #$params['content']              = parse('mail/paymentreceived',$params,null,'webshops',$params['order']['hostname']);
        $html                           = parse('mail/container',$params,null,'webshops');

        self::sendMail($fromEmail,$toEmail,$params['order']['cp_firstname'].' '.$params['order']['cp_lastname'],'Out of order',$html);                   
        #exit($html);
    }
    
    
    
    
    
    
                                    
    static function sendOrderPickedMail($params,$orderId){        
        $params['order']                = OrderDao::getOrder($orderId);                        
        $params['hostname']             = baseHost($params['order']['hostname']);                 
        $params['relation']             = RelationDao::getById($params['order']['relation_id']);
        $lang                           = Lang::getCodeByLanguageId($params['order']['fk_locale']);        
        $lang                           = empty($lang)?'nl':$lang;
        $translation                    = Translate::getTranslationNoInject($lang,'mail_orderpicked','webshops');        
        if(!empty($translation))
            $params                     = array_merge($params,$translation);                            
        $params['company_name']         = Webshop::getWebshopSetting($params['order']['hostname'],'company_name');
        $params['company_email']        = Webshop::getWebshopSetting($params['order']['hostname'],'contact_email');        
        $params['company_phone']        = Webshop::getWebshopSetting($params['order']['hostname'],'contact_phone');
        $params['mailing_color']        = Webshop::getWebshopSetting($params['order']['hostname'],'mailing_color');
        $params['mailing_txt_color']    = Webshop::getWebshopSetting($params['order']['hostname'],'mailing_txt_color');			        
        $params['content']              = parse('mail/orderpicked',$params,null,'webshops');                
        $html                           = parse('mail/container',$params,null,'webshops');
        self::sendMail($params['company_email'],$params['order']['email'],$params['order']['cp_firstname'].' '.$params['order']['cp_lastname'],$translation['trans_mailsubject'],$html);              
    }       
    static function sendPaymentMail($params,$orderId){
        $params['order']                = OrderDao::getOrder($orderId);
        $params['hostname']             = baseHost($params['order']['hostname']);
        $translation                    = Translate::getTranslationNoInject($lang,'mail_paymentok','webshops');
        if(!empty($translation))
            $params                     = array_merge($params,$translation);                        
        $params['company_name']         = Webshop::getWebshopSetting($params['hostname'],'company_name');
        $params['company_email']        = Webshop::getWebshopSetting($params['hostname'],'contact_email');        
        $params['company_phone']        = Webshop::getWebshopSetting($params['hostname'],'contact_phone');
        $params['mailing_color']        = Webshop::getWebshopSetting($params['hostname'],'mailing_color');
        $params['mailing_txt_color']    = Webshop::getWebshopSetting($params['hostname'],'mailing_txt_color');
                                
        if(is_array($params['order_items']))
            foreach($params['order_items'] as $id=>$data)
                $params['order_items'][$id]['bgcolor'] = ($id & 1)?'#F6F6F6':'#FFF'; 
        
        $params['content']              = parse('mail/paymentreceived',$params,null,'webshops',$params['order']['hostname']);
        $html                           = parse('mail/container',$params,null,'webshops',$params['order']['hostname']);        

        self::sendMail($params['company_email'],$params['order']['email'],$params['order']['cp_firstname'].' '.$params['order']['cp_lastname'],'Betaling ontvangen',$html);                   
        #exit($html);
    }
    static function sendOrderMail($params,$orderId){
        $params['order']                = OrderDao::getOrder($orderId);
                
        $lang                           = Lang::getCodeByLanguageId($params['order']['fk_locale']);
        
        $params['hostname']             = baseHost($params['order']['hostname']);
        $params['order_items']          = OrderDao::getOrderItems($orderId);
        
        if($lang!='nl'){
            $params['order']['paymethod_visible'] = str_replace('Contant of Pin bij afhalen','Cash when pricing up',$params['order']['paymethod_visible']);
        }
        $translation                    = Translate::getTranslationNoInject($lang,'mail_sendorder','webshops'); 
        if(!empty($translation))
            $params                     = array_merge($params,$translation);                  
        $params['company_name']         = Webshop::getWebshopSetting($params['hostname'],'company_name');
        $params['company_email']        = Webshop::getWebshopSetting($params['hostname'],'contact_email');        
        $params['company_phone']        = Webshop::getWebshopSetting($params['hostname'],'contact_phone');
        $params['mailing_color']        = Webshop::getWebshopSetting($params['hostname'],'mailing_color');
        $params['mailing_txt_color']    = Webshop::getWebshopSetting($params['hostname'],'mailing_txt_color');      
        if($lang=='en')
            $tmplang = 'gb';
        else
            $tmplang = $lang;
                 
        $params['bank_details']         = nl2br(Cfg::getPref('billing_thankyouorder_'.$tmplang));                                
        #_d($params['bank_details']);

        
        $params['subtotal']             = ShoppingbasketDb::getSubtotal($orderId);
        $params['total']                = ShoppingbasketDb::getTotal($orderId);
        
        $params['order_items']          = ShoppingbasketDb::getBasket($orderId);
        
        foreach($params['order_items'] as &$orderItem){
            $colors     =  ColorDao::getProductColors($orderItem['product_id']);
            
            $joinColors = array();
            if(!empty($colors)){
                foreach($colors as $color){
                    $joinColors[] = $color['color'];
                }
            }
            $orderItem['colors']        = join(',',$joinColors);
            $orderItem['size_label']    = TranslatedLookup::getTranslatedValue('product_size',Lang::getCodeByLanguageId($lang),$orderItem['fk_size']);         
        }        
        
        $params['delivery']             = ShoppingbasketDb::getDeliveryPrice($orderId,true);        
        $params['delivery_vat']         = ShoppingbasketDb::getDeliveryPrice($orderId,true,true);


        $params['transaction']          = ShoppingbasketDb::getTransactionFeeVis($orderId);
        $params['transaction_vat']      = ShoppingbasketDb::getTransactionFeeVis($orderId,true);        
        $params['vat']                  = ShoppingbasketDb::getVat($orderId);                                     
        $params['content']              = parse('mail/thankorder_b2c', $params, null, 'webshops', $params['order']['hostname']);
        $html                           = parse('mail/container', $params, null, 'webshops', $params['order']['hostname']);

        $toName                         = $params['order']['cp_firstname'].' '.$params['order']['cp_lastname'];
        if($lang=='nl')
        {
            $subject                        = 'Bedankt voor uw bestelling';
        }else{
            $html                           = str_replace('overboeking','banktransfer',$html);
            $html                           = str_replace('Overboeking','Banktransfer',$html); 
            $subject                        = 'Thanks for your order';
        }   
        self::sendMail($params['company_email'],$params['order']['email'],$toName,$subject,$html);                
        $cc_ordermail                   = Webshop::getWebshopSetting($params['hostname'],'cc_ordermail');
        $cc_ordermailaddress            = Webshop::getWebshopSetting($params['hostname'],'cc_ordermailaddress');
        
        if($cc_ordermail==1)
            self::sendMail($params['company_email'],$cc_ordermailaddress,$toName,'KOPIE '.$subject,$html);        
    }
    
    static function sendReviewMail($params){		 		
        if(!empty($translation))
            $params                         = array_merge($params,$translation);                  
        $params['company_name']             = Webshop::getWebshopSetting($params['hostname'],'company_name');
        $params['company_email']            = Webshop::getWebshopSetting($params['hostname'],'contact_email');        
        $params['company_phone']            = Webshop::getWebshopSetting($params['hostname'],'contact_phone');
        $params['mailing_color']            = Webshop::getWebshopSetting($params['hostname'],'mailing_color');
        $params['mailing_txt_color']        = Webshop::getWebshopSetting($params['hostname'],'mailing_txt_color');                
        $params['bank_details']             = nl2br(Cfg::getPref('billing_thankyouorder')); 
        $params['hostname']                 = $_SERVER['SERVER_NAME'];
        $params['trans_withkindregards']    = 'Met vriendelijke groet';                               		
        $params['content']                  = parse('mail/review',$params,null,'webshops',$params['order']['hostname']);
        $html                               = parse('mail/container',$params,null,'webshops',$params['order']['hostname']);      
        
        self::sendMail($params['webshop_settings']['mailing_email'],$params['sendmailto'],'Review moderatie','Review moderatie',$html);   
	}
	
	/**
	* @param $email ...
	* @param $vars an array of vars that should be replaced in the template
	* @param $tag refers to a template + translation
	
	static function sendMail($from,$toMail,$toName,$subject,$html){	   
            if($_SERVER['IS_DEVEL'])
                $toMail   = 'info@nuicart.nl';	   
            Log::message('maillog',"Mail: $subject $toMail",__METHOD__);
            require_once('./classes/mail/Mail.php');            
            $mail = new Mail($toMail, $subject, $from, $from);
            $mail->setHtml($html);            
            $mail->send();
	}
    */
}