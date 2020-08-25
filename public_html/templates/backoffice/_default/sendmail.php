<?php
class SendMail{
    
    /**
     * sendOrderMail::run()
     * Calling this method with an ordernumber will result in sending out an email
     * 
     * @param mixed $params
     * @return void
     */
    public static function run($params){        
        if($params['_do']=='orderpickedmail')            
			Mailer::sendOrderPickedMail($params,$params['orderid']);
		exit();
    }   
}