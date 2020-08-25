<?php
class Test{
    function  run($params){
        
        $params['data'] = array(
            1=> array('naam'=>'aap'),
            2=> array('naam'=>'noot'),
            3=> array('naam'=>'mies'),
            4=> array('naam'=>'wim'),
            5=> array('naam'=>'baart'),
        );   
        exit(parse('test',$params));
        
    }
}