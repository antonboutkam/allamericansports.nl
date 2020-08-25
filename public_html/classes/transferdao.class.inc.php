<?php
class TransferDao{

    public static function create($did,$locationId){
        $sql = sprintf('INSERT INTO transfer (did,location_id) VALUES(%d,%d)',$did,$locationId);
        query($sql,__METHOD__);
    }
}