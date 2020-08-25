<?php
require_once('mailchimp/src/Mailchimp.php');
class MailChimpDao{
    public static function getOurList($mailChimp,$listName){
        
        $lists          = new Mailchimp_Lists($mailChimp);
        $lists          = $lists->getList(array('list_name'=>$listName));
        
        if($lists['data'][0]['name'] == $listName)
            return $lists['data'][0];         
    }
    public static function batchSync($mailChimp,$limit,$listId){
        // Need to make this based on the newsletter subscriptions
        
        $sql        = sprintf('SELECT 
                                    n.email,
                                    n.id,
                                    r.cp_firstname,r.cp_lastname 
                                FROM 
                                    newsletter n 
                                LEFT JOIN relations r ON n.email=r.email  
                                WHERE 
                                    (n.mailchimp_synced=0 OR n.mailchimp_synced IS NULL)  
                                LIMIT '.$limit);
        $accounts   = fetchArray($sql,__METHOD__);

        $lists      = new Mailchimp_Lists($mailChimp);
        if(!empty($accounts)){
            foreach($accounts as $account){
                //_d($account);
                $emails[]   = array(
                                    'email'         =>   array('email'=>$account['email']),
                                    'email_type'    =>  'html',
                                    'merge_vars'    =>  array(
                                    'FNAME'=>$account['cp_firstname'],
                                    'LNAME'=>$account['cp_lastname'],
                                    ));
                $ids[]      = $account['id']; 
            }
            $sql = sprintf('UPDATE newsletter SET mailchimp_synced=1 WHERE id IN(%s)',join(',',$ids));
            
            query($sql,__METHOD__);
            #echo "listId $listId<Br>";
            #pre_r($emails);
            $result =$lists->batchSubscribe($listId,$emails,false,true,true);
            #pre_r($result);    
        }        
        
    }
    public static function getUnsyncedRelationCount(){
        $sql = sprintf('SELECT COUNT(n.id) quantity  
                        FROM newsletter n 
                        LEFT JOIN relations r ON n.email=r.email
                        WHERE
                        (n.mailchimp_synced=0 OR n.mailchimp_synced IS NULL)  ');;
        return fetchVal($sql,__METHOD__);        
    }           
    public static function getUnsyncedRelations(){
    //    sprintf()
    }       
}