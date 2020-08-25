<?php
class Weightbased implements ShippingCost{
    //put your code here
    function moduleDesc(){
        return 'Weight based calculation';
    }
    function configFields(){
        return array(
                'send_cost_light'               => 'Verzendkosten tot 3kg (ex btw)',
                'send_cost_medium'              => '3 tot 15kg (ex btw)',
                'send_cost_heavy'               => 'Verzendkosten 15 tot 32kg (ex btw)',                
                'send_cost_international'       => 'Verzendkosten internationaal (ex btw)'
            );
    }    
    function productEditTemplate(){
        // Werkt op basis van gewicht, 
        return false;
    }    
    function calcShippingCost(){
        return 10;
    }    
}
