<?php
class ExactXmlLog{
    public static function logOrderXml($orderId,$inOut,$xml){
        
        $xml = trim(removeBom($xml));
        $sql = sprintf('INSERT INTO exact_order_log (fk_order,`created_date`,`inout`,`xml`)
                        VALUE(%d,NOW(),"%s","%s")',
                        $orderId,$inOut,quote($xml));

        query($sql,__METHOD__);
    }
    public static function getOrderLog($orderId){
        $sql    = sprintf('SELECT * FROM exact_order_log WHERE fk_order=%d ORDER BY `created_date` DESC',$orderId);
        
        $data   = fetchArray($sql,__METHOD__);
        if(empty($data)){
            return array();
        }
        foreach($data as $id=>$row){
            $data[$id]['xml'] = htmlentities(trim($row['xml']));
        }
        return $data; 
        
    }
}