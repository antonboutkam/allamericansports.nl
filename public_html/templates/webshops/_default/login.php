<?php
class Login{
    function  run($params){
        
        if($params['r']){
            $params['r'] = htmlentities($params['r']);
        }
        if($params['request_uri'] == '/login.html'){
            redirect('/nl/login.html');
        }
        
        $params['title_override']   = 'Inloggen, uitloggen, wachtwoord resetten';        
        $params                     = Webshop::doFirst($params);
        
        if($params['logout']==1){
            RelationDao::logout();
            redirect($params['root'].'/login.html');
        }


        if($params['_do']=='login'){

            $credentialsOk = RelationDao::login($params['emailaddr'],$params['password']);

            if(!$credentialsOk){
                $params['login_failed'] = !$credentialsOk;
            }
        }
        
        if($params['_do']=='reset'){        
            $relation = RelationDao::getBy('LOWER(r.email)',strtolower(quote(strip_tags($params['email']))));
            
            if(isset($relation['id'])){
                $relation['new_pass'] = substr(md5(time().$relation['email']),2,8);
                $relation['password'] = $relation['new_pass'];   
                           	    
                RelationDao::store($relation,$relation['id']);
                
                $from = Webshop::getWebshopSetting($params['host'],'contact_email');                
                $relation['host']  = sprintf('http://%s',$_SERVER['HTTP_HOST']);                                               
                $html = parse('mail/passreset',$relation);                                   
                Mailer::sendMail($from, $relation['email'],$relation['cp_firstname'].' '.$relation['cp_lastname'],'Uw nieuwe wachtwoord',$html);
            }else{
                $params['not_found'] = true;
            }	
        }    
        if(RelationDao::isMember()){
            if($params['r']){
                // Remove leading slash and then add a leading slash to make sure there is always only 1 and at least 1.
                redirect('/'.preg_replace('/^\//','',$params['r']));
            }else{
                if($params['lang']){
                    redirect('/'.$params['lang'].'/myaccount.html');    
                }else{
                    redirect('/myaccount.html');
                }
                
			}	
        }
        $params['cart_items']   = Shoppingbasket::getTotalQuantity();
        $params['types']        = ProductTypeDao::getWebshopProductTypes($params['hostname'],true);
        $params['content']      = parse('login',$params);
         
        return $params;
    }

}