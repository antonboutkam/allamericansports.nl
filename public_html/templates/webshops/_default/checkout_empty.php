<?php
class Checkout_empty{
    
    function run($params){        
        unset($_SESSION['basket_db']);
        Shoppingbasket::clear();
        
        $params              =  Webshop::doFirst($params);
        $params['content']   =  parse('checkout_empty',$params);
        return $params;        
    }
}        