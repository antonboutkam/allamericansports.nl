<?php
class ProductSerial{
    public static function delete($id){
        $sql = sprintf('DELETE FROM product_serial WHERE id=%d',$id);
        query($sql,__METHOD__);
    }
    public static function takeIn($id){
        query($sql = sprintf('UPDATE product_serial SET delivered=NULL,order_id=NULL,relation_id=NULL WHERE id=%d',$id),__METHOD__);
    }
    public static function giveOut($orderId,$serialId){
        if($field=='serial')
            $serialId = self::stripCrap($serialId);
        if(!self::inDb($serialId,'serial'))
            self::register($serialId);
            
        query($sql = sprintf('
                UPDATE
                    product_serial
                SET delivered=NOW(),
                    order_id=%1$d,
                    relation_id=(SELECT relation_id FROM orders WHERE id=%1$d)
                WHERE
                    serial="%2$s"',$orderId,$serialId),__METHOD__);
    }
    private static function stripCrap($serial){
        /*
        if(preg_match('/^CN|CZ/',$serial)){
            $serial = preg_replace('/^([A-Z]{2})/','',$serial);
            $serial = substr($serial,0,7);
        }
         */
        return $serial;
    }
    public static function register($serial){
        $serial = self::stripCrap($serial);
        query($sql = sprintf('INSERT IGNORE INTO product_serial (serial,added) VALUE("%s",NOW())',$serial),__METHOD__);
    }

    public static function inDb($id,$field='id'){
        if($field=='serial')
            $id = self::stripCrap($id);
        $sql        = sprintf('SELECT serial FROM product_serial WHERE `%s` = "%s"',$field,$id);
        
        $serial     = fetchVal($sql,__METHOD__);
        if($serial)
            return true;
        return;
    }
    public static function orderSerials($orderId){
        $sql = sprintf('SELECT ps.*							
                        FROM
                            product_serial ps                                                       
                        WHERE ps.order_id = "%s"',$orderId);			
        $data =  fetchArray($sql,__METHOD__);		
        return $data;
    }
    public static function find($query=null,$currentPage=1,$itemsPP=20){
        if($query)
           $query = sprintf('WHERE serial LIKE "%%%s%%"',$query);
        $sql    = sprintf('SELECT SQL_CALC_FOUND_ROWS ps.*,r.company_name FROM product_serial ps LEFT JOIN relations r ON r.id=ps.relation_id %s ORDER BY id DESC LIMIT %d, %d ',
                            $query,($currentPage*$itemsPP-$itemsPP),$itemsPP);
        
        $result['data']     = fetchArray($sql,__METHOD__);
        $result['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        return $result;
    }
}
