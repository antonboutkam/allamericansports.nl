<?php
class CreateAccount{
    public static function run($params){
        if($params['_do'] =='create_account'){
                                    
            if($params['check_code']!='abcdefg'){
                exit('spammer detected');            
            }            
            $params['relation']['webshop']  = $params['current_webshop_id'];
            $params['relation']['type']     = 'prospect';
            RelationDao::store($params['relation'], null);
            RelationDao::login($params['relation']['email'],$params['relation']['password']);
            redirect($params['root'].'/');
        }
        $params['title_override']               = 'Account aanmaken';
        $params                                 = Webshop::doFirst($params);
        $params['settings']                     = Cfg::getPrefs();
        $params['webshop_companyname']          = Webshop::getWebshopSetting($params['hostname'],'company_name');

        $params['content']      = parse('createaccount',$params);
        return $params;
    }
}