<?php
class Countryiso{
    public static function getAll(){
            return fetchArray('SELECT * FROM country_iso ORDER BY name',__METHOD__);
    }
    public static function getNameByIso2Code($isoCode){
        $tpl        = 'SELECT name FROM country_iso WHERE iso2 ="%s"';
        $isoCode    = strtoupper($isoCode);
        $sql        = sprintf($tpl,$isoCode);
        return fetchVal($sql,__METHOD__); 
    }
}