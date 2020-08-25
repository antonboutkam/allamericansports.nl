<?php
class Nocost implements ShippingCost{
    //put your code here
    function moduleDesc(){
        return 'Geen verzendkosten';
    }
    function configFields(){
        return array();
    }    
    function productEditTemplate(){
        return false;
    }      
    function calcShippingCost(){
        return 0;
    }    
}
