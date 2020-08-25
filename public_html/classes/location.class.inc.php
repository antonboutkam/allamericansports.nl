<?php
class Location{
    public static function getName($locationId){
        if(!$locationId)
            return;
        return fetchVal($sql = sprintf('SELECT name FROM warehouse_locations WHERE id=%d', $locationId),__METHOD__);
    }
}