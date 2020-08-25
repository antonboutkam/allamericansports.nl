<?php
class MailLogDao{
    
    static function getXlatestEmailsTo($toEmail,$count=20){
        $sql = sprintf('SELECT id,tomail,frommail,subject,mailtime FROM mail_log WHERE tomail="%s" ORDER BY mailtime DESC LIMIT %d',$toEmail,$count);         
        $out = fetchArray($sql,__METHOD__);
        return $out; 
    }
    
    static function getMailbyId($id){
        $sql = sprintf('SELECT * FROM mail_log WHERE id="%d"',$id);                 
        return fetchRow($sql,__METHOD__);         
    }    
}