<?php
class Settings_user_edit{
    function  run($params){
        $params['current_user']         = User::getCurent();        
        $params['developer']            = ($params['current_user']['level']=='developer')?1:0;
        
        if($params['_do']){
            if($params['user']['level']=='developer' && !$params['developer']){
                // Alleen developers mogen developers aanmaken.
                $params['user']['level'] = 'admin';
            }
            if(trim($params['user']['pass'])!=''){                
                $params['user']['pass'] = md5($params['user']['pass']);
            }else{
                $params['user']['pass'] = '';
            }                
            $id = User::store($params['user'],$params['id']);
            if(is_numeric($id)&&$id!=0)
                $params['id'] = $id;                 
        }
        if(isset($params['id']) && $params['id']!='new'){
            $user          =   User::getById( $params['id']);
            unset($user['pass']);
            if(is_array($user))
                $params    =   array_merge($params,$user);   
        }            
        return $params;
    }
}