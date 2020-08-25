<?php
class Presignin{
    function run($params){
        $params             = Webshop::doFirst($params);
        $params['content']  = parse('presignin',$params);
        return $params;
    }
}