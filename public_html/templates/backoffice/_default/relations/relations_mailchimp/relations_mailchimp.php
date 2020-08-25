<?php
class Relations_mailchimp{
    function  run($params){  
         if(strpos(User::getLevel(),'w')===0){	
          redirect('/');	
          exit();
        }
        
        
        require_once('mailchimp/src/Mailchimp.php');

        if(!isset($params['ajax']) && !isset($params['done']))
        	query('update newsletter set mailchimp_synced=0',__METHOD__);
	/*
        if(!isset($_SESSION['mailchimp_synced'])){
            query('update relations set mailchimp_synced=0',__METHOD__);
            $_SESSION['mailchimp_synced'] = true;
        }
*/
        if($params['_do'] == 'configure_mailchimp'){            
            Cfg::storePref('mailchimp_configured',1);
            Cfg::storePref('mailchimp_apikey',$params['mailchimp_apikey']);
            Cfg::storePref('mailchimp_listname',$params['mailchimp_listname']);
        }                                
        if(!Cfg::getPref('mailchimp_configured'))
            $params['_do'] = 'mailchimp_reconfigure';                               
        
        $params['mailchimp_listname']   = Cfg::getPref('mailchimp_listname');
        $params['mailchimp_apikey']     = Cfg::getPref('mailchimp_apikey');

        
        if($params['_do'] == 'mailchimp_reconfigure'){
            $params['content'] = parse('mailchimp_configure',$params,__FILE__);
            return $params;
        }                                                        
        $mailChimp                      = new Mailchimp(Cfg::getPref('mailchimp_apikey'),$opts);                      
        $params['our_list']             = MailChimpDao::getOurList($mailChimp,$params['mailchimp_listname']);
        
                                
        if($params['_do']=='sync_relations')                        
            MailChimpDao::batchSync($mailChimp,250,$params['our_list']['id']);                            
                        
        if(empty($params['our_list'])){
            $params['content'] = parse('mailchimp_configerror',$params,__FILE__);
            return $params;   
        }
        
        $params['unsynced_relationcount']   =  MailChimpDao::getUnsyncedRelationCount();        
        
        $params['content']                  = parse('relations_mailchimp',$params,__FILE__);
        return $params;
    }
}
