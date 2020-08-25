<?php
class Settings_modules{
    function  run($params){
        
        if($params['_do']=='delete_user')
            User::delete($params['id']);
        if($params['_do']=='delete_paymethod')
            Paymethod::delete($params['id']);


        $params['sort']             =   ($params['sort'])?$params['sort']:'full_name';                    
        $users                      =   User::getAll($params['sort']);
        $params['users']            =   $users['data'];
        $params['rowcount']         =   $users['rowcount']; 
        $params['userlist_tbl']     =   parse('inc/userlist_tbl',$params);
        
        $paymethods                 =   Paymethod::getAll('name');
        $params['paymethods']       =   $paymethods['data'];
        $params['rowcount']         =   $paymethods['rowcount'];         
        $params['paymethods_tbl']   =   parse('inc/paymethods_tbl',$params);  
        return $params;
    }
}