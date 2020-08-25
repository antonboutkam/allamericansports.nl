 page   <?php
class Oldbills {
    public static function getByOldCustomerId($customerId){
        $sql = sprintf('SELECT * FROM bills_old WHERE klant_id=%d',$customerId);        
        return fetchArray($sql,__METHOD__);
    }
}
