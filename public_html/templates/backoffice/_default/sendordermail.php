<?php
class sendOrderMail{
    
    /**
     * sendOrderMail::run()
     * Calling this method with an ordernumber will result in sending out an email
     * 
     * @param mixed $params
     * @return void
     */
    public static function run($params){
        if($params['send']=='orderpickedmail')
            $template = '../../webshops/laptopcentrale.nl';
    }
    
}