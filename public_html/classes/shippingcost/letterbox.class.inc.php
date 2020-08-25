<?php
/* Deze queries zijn nodig voor deze module
 * ALTER TABLE catalogue ADD COLUMN max_in_envelope INT(11) NULL;
 * ALTER TABLE catalogue ADD COLUMN fit_in_envelope TINYINT(1) NOT NULL DEFAULT 0;
 * UPDATE catalogue SET fit_in_envelope =1;
 * UPDATE catalogue SET max_in_envelope =3;
 * ALTER TABLE catalogue DROP COLUMN package_box;
 */
class Letterbox implements ShippingCost{

    function moduleDesc(){
        return 'Gebaseerd op briefpost of pakketpost';
    }
    function configFields(){
        return array(
                'sendcost_letter'           => 'Prijs bij briefpost (ex btw)',
                'sendcost_box'              => 'Prijs bij pakketpost (ex btw)',
                'sendcost_international'    => 'Verzendkosten internationaal (ex btw)'                
            );
    }
    function productEditTemplate(){
        return 'module_sendcost_letterbox';
    }
    function calcShippingCost($hostname,$delivery,$internationalOrder,$productBasket){        
        #pre_r($productBasket);    
        
        #echo $hostname.', delivery:'.$delivery.' international:'.$internationalOrder.' productBasket:'.$productBasket;
         if($delivery && $internationalOrder){                          
             return (float)Webshop::getWebshopSetting($hostname, 'sendcost_international'); // 10.95 ex btw;
         }else if($delivery){
             // Kijken of het een doosje of een envelop wordt...             
             if(!empty($productBasket)){
                foreach($productBasket as $item){
                    if($item['fit_in_envelope']==0)
                        return (float)Webshop::getWebshopSetting($hostname, 'sendcost_box');
                    else{                        
                        $perc = $perc + ((100/$item['max_in_envelope'])*$item['order_quantity']);
                    }
                }                
                if($perc>100){
                    // Envelop zit voor meer dan 100% vol...
                    return (float)Webshop::getWebshopSetting($hostname, 'sendcost_box');
                }
             }
             // De bestelling past in een envelop.   
             return Webshop::getWebshopSetting($hostname, 'sendcost_letter'); //2,50 ex btw            
         }else{
             // De bezoeker kom thet pakketje afhalen
             return 0;
         }
        
    }
}