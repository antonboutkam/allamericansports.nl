<?php
class Debuginfo{    
    function run($params){        
        unset($_SESSION['basket_db']);
        #$params              =  Webshop::doFirst($params);
        
        
        if($params['_do'] == 'set_activetab')                       
           $_SESSION['debug']['active_tab'] = $params['active_tab'];
        if(!isset($_SESSION['debug']['active_tab']))
            $_SESSION['debug']['active_tab'] = 'template_panel';
        
        $params['active_tab'] = $_SESSION['debug']['active_tab'];
        
        if($params['_do'] == 'set_state'){            
            $_SESSION['debug']['state'] = $params['new_state'];            
            exit();
        }
        if(strpos($params['request_uri'], '/checkout.html'))        
            $params['checkout_form'] = true;
                    
        
        if(!isset($_SESSION['debug']['state']))
            $_SESSION['debug']['state']= 'closed';
        
        if(!empty($_SESSION['console']['log'])){
            $params['console_messages']         = join('<br />',$_SESSION['console']['log']);                 
            $_SESSION['console']['log']         = null;
        }
        
        
        $params['code_base']                = SITE_ROOT;                        
        $params['session']                  = $_SESSION;
        
        $params['prer']                     = print_r($params,true);
        $params['content']                  = parse('debuginfo',$params);                
        return $params;        
    }
}        