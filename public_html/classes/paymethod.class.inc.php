<?php
class Paymethod{
    /**
     * Paymethod::getAll()
     * 
     * @param mixed $sort
     * @param bool $asInWebshop Sommige betaalmethoden zijn alleen in de backend zichtbaar. Zet deze op true wanneer je in de webshop werkt.
     * @param mixed $postOrderPaymethodsOnly [false|0|1] 0 = alleen pickup_paymethod, 1 = alleen postorder_paymethod, false = beiden 
     * @return
     */
    public static function getAll($sort=null,$asInWebshop=false,$postOrderPaymethodsOnly=false){
        $where = '';
        
        if($sort)
            $sort = sprintf("ORDER BY %s",$sort);
        if($asInWebshop)  
            $where =  'AND in_webshop=1 ';  
            
        if($postOrderPaymethodsOnly===1){
            $where .=  'AND postorder_paymethod=1 ';  
        }    
        if($postOrderPaymethodsOnly==='0' || $postOrderPaymethodsOnly===0){            
            $where .=  'AND pickup_paymethod=1 ';  
        }    
        $result['data'] = fetchArray($sql = sprintf('SELECT 
                                        SQL_CALC_FOUND_ROWS * 
                                       FROM paymethods 
                                       WHERE is_deleted=0
                                       %s 
                                       %s',
                                       $where,
                                       $sort),__METHOD__);                                   
        
        $result['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        return $result;             
    }
    public static function store($user,$id){
        return Db::instance()->store('paymethods',array('id'=>$id),$user);        
    } 
    public static function getById($id){                                                   
        return fetchRow(sprintf('SELECT * FROM paymethods WHERE id=%d',$id),__METHOD__);
    }  
    public static function delete($id){
        return query($sql = sprintf('UPDATE paymethods SET is_deleted=1 WHERE id=%d',$id),__METHOD__);
    }      
}