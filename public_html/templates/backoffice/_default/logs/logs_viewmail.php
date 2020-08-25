<?php
class Logs_viewmail{
    function  run($params){
         if(strpos(User::getLevel(),'w')===0){	
          redirect('/');	
          exit();
        }
        $params['mail']         = MailLogDao::getMailbyId($params['id']);                                                                    
        return $params;
    }
}