<?php
class Customshipping implements ShippingCost{
    //put your code here
    function moduleDesc(){
        return 'Calculation based on shipping country with free shipping above a certain level';
    }
    function configFields(){
        $out = array(
		
						'send_cost_nederland' => 'Verzendkosten inc btw voor Nederland',
						'send_cost_nederland_freefrom' => 'Verzendkosten inc btw voor Nederland gratis boven',
						'send_cost_be' => 'Verzendkosten inc btw voor Belgie',
						'send_cost_de' => 'Verzendkosten inc btw voor Duitsland',
						'send_cost_dk' => 'Verzendkosten inc btw voor Denemarken',
						'send_cost_fr' => 'Verzendkosten inc btw voor Frankrijk',
						'send_cost_it' => 'Verzendkosten inc btw voor Italie',
						'send_cost_lu' => 'Verzendkosten inc btw voor Luxemburg',
						'send_cost_at' => 'Verzendkosten inc btw voor Oostenrijk',
						'send_cost_es' => 'Verzendkosten inc btw voor Spanje',
						'send_cost_gb' =>  'Verzendkosten inc btw voor het Verenigd Koninkrijk',
						'send_cost_se' => 'Verzendkosten inc btw voor Zweden',
						'send_cost_ch' => 'Verzendkosten inc btw voor Zwitserland',
						'send_cost_ir' => 'Verzendkosten inc btw voor Ierland',
						'send_cost_bg' => 'Verzendkosten inc btw voor Bulgarije',
						'send_cost_ee' => 'Verzendkosten inc btw voor Estland',
						'send_cost_fi' => 'Verzendkosten inc btw voor Finland',
						'send_cost_hu' => 'Verzendkosten inc btw voor Hongarije',
						'send_cost_lv' => 'Verzendkosten inc btw voor Letland',
						'send_cost_lt' => 'Verzendkosten inc btw voor Litouwen',
						'send_cost_pl' => 'Verzendkosten inc btw voor Polen',
						'send_cost_pt' => 'Verzendkosten inc btw voor Portugal',
						'send_cost_ro' => 'Verzendkosten inc btw voor Roemenie',
						'send_cost_si' => 'Verzendkosten inc btw voor Slovenie',
						'send_cost_sk' => 'Verzendkosten inc btw voor Slowakije',
						'send_cost_cz' => 'Verzendkosten inc btw voor Tsjechie',
						'send_cost_al' => 'Verzendkosten inc btw voor Albanie',
						'send_cost_ad' => 'Verzendkosten inc btw voor Andorra',
						'send_cost_ba' => 'Verzendkosten inc btw voor Bosnie Herzegovina',
						'send_cost_ky' => 'Verzendkosten inc btw voor Canarische Eilanden',
						'send_cost_cy' => 'Verzendkosten inc btw voor Cyprus',
						'send_cost_fo' => 'Verzendkosten inc btw voor Faeroer',
						'send_cost_gi' => 'Verzendkosten inc btw voor Gibraltar',
						'send_cost_gr' => 'Verzendkosten inc btw voor Griekenland',
						'send_cost_gl' => 'Verzendkosten inc btw voor Groenland',
						'send_cost_is' => 'Verzendkosten inc btw voor Ijsland',
						'send_cost_ch' => 'Verzendkosten inc btw voor Kanaaleilanden',
						'send_cost_kosovo' => 'Verzendkosten inc btw voor Kosovo',
						'send_cost_hr' => 'Verzendkosten inc btw voor Kroatie',
						'send_cost_li' => 'Verzendkosten inc btw voor Liechtenstein',
						'send_cost_mk' => 'Verzendkosten inc btw voor Macedonie',
						'send_cost_mt' => 'Verzendkosten inc btw voor Malta',
						'send_cost_md' => 'Verzendkosten inc btw voor Moldavie',
						'send_cost_mo' => 'Verzendkosten inc btw voor Montenegro',
						'send_cost_no' => 'Verzendkosten inc btw voor Noorwegen',
						'send_cost_uk' => 'Verzendkosten inc btw voor Oekraine',
						'send_cost_sm' => 'Verzendkosten inc btw voor San Marino',
						'send_cost_rs' => 'Verzendkosten inc btw voor Servie',
						'send_cost_tr' => 'Verzendkosten inc btw voor Turkije',
						'send_cost_va' => 'Verzendkosten inc btw voor Vaticaanstad',
						'send_cost_by' => 'Verzendkosten inc btw voor Wit Rusland',
						'send_cost_ru' => 'Verzendkosten inc btw voor Rusland',
						'send_cost_other' => 'Verzendkosten inc btw voor Alle overige landen');
		#pre_r($out);
		return $out;
    }    
    function productEditTemplate(){
        // Werkt op basis van gewicht, 
        return false;
    }    
    function calcShippingCost($hostname,$delivery,$internationalOrder,$basket){
        $member = RelationDao::getMember();                
        $fields = self::configFields();
        $vat = '1'.Cfg::getPref('btw'); // 21            

        // Default country is NL, shipping when given else billing is same as shipping.
        if(empty($member['billing_country']) && empty($member['shipping_country'])){
            $country = 'Nederland';
        }else if(!empty($member['billing_country'])){
            $country = $member['billing_country'];
        }

        if(strtolower($country)=='the netherlands'){
            $country = 'nederland';
        }
        #echo $country;
        $country            = 'send_cost_'.strtolower($country);                
        $setting_tag        = str_replace(' ','_',$country);
        $setting_tag        = str_replace('(','',$setting_tag);
        $setting_tag        = str_replace(')','',$setting_tag);

        if($setting_tag=='send_cost_nl'){
            // In the netherlands the shipping is free above an x Amount of money.
            // In order to accept both , and . we convert the amounts int cents. 
            // Hoping / assuming the user is not using thousand separators to
            $totalBasketValue = str_replace(array(',','.'),'',self::getTotalBasketValue());
            
            #echo "total basket value ".self::getTotalBasketValue()."<br>";
            
            $hollandFreeShippingTresshold = Webshop::getWebshopSetting($hostname, 'send_cost_nederland_freefrom');
            if(!strpos($hollandFreeShippingTresshold,'.') && !strpos($hollandFreeShippingTresshold,',')){
                $hollandFreeShippingTresshold = $hollandFreeShippingTresshold.'.00';
            }            
            $hollandFreeShippingTresshold = str_replace(array('.',','),'',$hollandFreeShippingTresshold);
            #echo $hollandFreeShippingTresshold.' <= '.$totalBasketValue;                                    
            if($hollandFreeShippingTresshold<=$totalBasketValue){                
                return 0;
            }else{                
                $sendCost = Webshop::getWebshopSetting($hostname, 'send_cost_nederland');
            }
        }else if(isset($fields[$setting_tag])){
            $sendCost = Webshop::getWebshopSetting($hostname, $setting_tag);
        }else{
            $sendCost = Webshop::getWebshopSetting($hostname, 'send_cost_other');            
        }
        
        $sendCost  = (float)(str_replace(array('.',','),'',$sendCost)/$vat)*100;
        $sendCost  = $sendCost/100;
        
        // This function expects to recieve an ex vat price.
                 
        #$sendCost = ($sendCost/$vat)*100;
                
        return $sendCost;
        /*
        
        pre_r($member);
        //pre_r($_SESSION['relation']);
        exit();
        pre_r($hostname);
        
        echo "<br>-------------<br>";
        pre_r($delivery);
        echo "<br>-------------<br>";
        pre_r($internationalOrder);
        echo "<br>-------------<br>";
        pre_r($basket);
        echo "<br>-------------<br>";        
         * 
         */
    }    
    private static function getTotalBasketValue(){
        if($_SESSION['basket_db'])
           return number_format(ShoppingbasketDb::getSubtotalUnformat($_SESSION['basket_db'],true),2,".","");        
        return number_format(Shoppingbasket::getSubtotalUnformat(true),2,".","");                                       
    }
}
