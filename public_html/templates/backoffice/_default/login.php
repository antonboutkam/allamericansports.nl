<?php
class Login{
    function  run($params){
        if(isset($_COOKIE['auto']) && $_COOKIE['auto']){
            User::autoLogin($_COOKIE['auto']);
            if(User::isMember())
                redirect($params['root'].'/');  
        }

        if(!isset($params['_do'])){
            $params['_do'] = null;
        }

        $params['system_name'] = Cfg::getPref('system_name');
        if($params['_do']=='reset'){
            $params['error'] = User::resetByEmail($params['email']);

        }else if($params['_do']=='login'){

            if(User::login($params)){
                if($params['remember_id'])
                    User::setAutologin();
					
				if(isset($_SESSION['after_login'])){	
					redirect($_SESSION['after_login']);                
				}else{
					redirect($params['root'].'/'); 
				}
            }else{
                $params['login_error'] = '1';
            }
        }
        foreach($params['locations'] as $location){
            if($location['country_warehouse']) {
                $params['country_warehouse_id'] = $location['id'];
            }
        }
            
        if(isset($params['ajax']) && $params['ajax']){
            echo json_encode($params);
            exit();
        }

        return $params;
    }
}