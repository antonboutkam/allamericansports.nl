<?php
class Statistics_googlevisitors{
    function  run($params){                
        $params['latest']   =   Statistics::getLastGoogleQueries(250);                                                        
        $params['content']   =   parse('statistics_googlevisitors',$params);        
        return $params;
    }    
    
}