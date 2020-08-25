<?php
class Exactonline_orderlog{
     function  run($params){
        $params['xml_log']  = ExactXmlLog::getOrderLog($params['orderid']);
        #pre_r($params['xml_log']);
        $params['content']  =  parse('exactonline_log',$params);
        return $params;
     }
}     
        