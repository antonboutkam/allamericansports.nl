<?php
class Thickness implements ShippingCost{
    //put your code here
    function moduleDesc(){
        return 'Berekend de verzendkosten obv de dikte';
    }
    function configFields(){
        return array(
				'fabric_tape'         		=> 'Hoeveel cm band past er in een envelop?',
                'fabric_extra_thin'         => 'Hoeveel cm extra dun (voile, katoen en micro  0.5 mm) materiaal past er in een envelop?',
                'fabric_thin'               => 'Hoeveel cm dun (ricot, satijn, taft zijde 1mm) past erin een envelop?',
                'fabric_middle'             => 'Hoeveel cm middel (vilt dun, nickey velours 2mm) past erin een envelop?',                
                'fabric_thick'              => 'Hoeveel cm dik (fleece, badstof, mantel 3mm) past er in een envelop?',
                'fabric_extrathick'         => 'Hoeveel cm extadik (borg en dikke vilt 4 a 5mm) past er in een envelop?',
                'sendcost_box'              => 'Verzendkosten pakket (ex btw)',
                'sendcost_letter'           => 'Verzendkosten envelop (ex btw)',
                'sendcost_international'    => 'Verzendkosten internationaal'
            );
    }    
    function productEditTemplate(){
        // Werkt op basis van gewicht, 
        return false;
    }    
    function calcShippingCost($hostname,$delivery,$internationalOrder,$productBasket){        
        
        #pre_r($productBasket);    
        $fields = array_keys($this->configFields());
        foreach($fields as $field){
            $settings[$field] = Webshop::getWebshopSetting($hostname,$field);
        }
        
        #echo $hostname.', delivery:'.$delivery.' international:'.$internationalOrder.' productBasket:'.$productBasket;
         if($delivery && $internationalOrder){                          
             return (float)Webshop::getWebshopSetting($hostname, 'sendcost_international'); // 10.95 ex btw;
         }else if($delivery){             
             $map = array(
							'0.001'		=>	'fabric_tape',
                            '0.005'		=>	'fabric_extra_thin',
                            '0.010'		=>	'fabric_thin',
                            '0.020'		=>	'fabric_middle',
                            '0.030'		=>	'fabric_thick',
                            '0.040'		=>	'fabric_extrathick',
                            '0.100'		=>	'requires_box');
             // Will it be a box or an envelope?             
             if(!empty($productBasket)){
                
                foreach($productBasket as $item){ 
                    #mail('anton@nui-boutkam.nl','test',print_r($productBasket,true));
                    #echo 'x'.$item['thicknesmm']." ".$settings[$map[$item['thicknesmm']]]."<br>";
                    $cm_in_envelope = $settings[$map[$item['thicknesmm']]];
                    
                    if($map[$item['thicknesmm']]=='requires_box'){
                        $perc = 101;
                    }else{                                        
                        // sample:
                        // (100/40 max in envelope) * 10 order quantity = 25% filled'
                        if($cm_in_envelope>0 && $item['order_quantity']>0){
                            $percAdd = (100/$cm_in_envelope)*$item['order_quantity'];
                            $perc = $perc + $percAdd;                    
                        }
                    }
                }                
                if($perc>100){                    
                    #exit('X '.$settings['sendcost_box']);
                    // Envelop zit voor meer dan 100% vol...
                    return (float)$settings['sendcost_box']; 
                }
             }
             // De bestelling past in een envelop.                
             return (float)$settings['sendcost_letter']; //2,50 ex btw            
         }else{
             // De bezoeker kom thet pakketje afhalen
             return 0;
         }        
    }
}
