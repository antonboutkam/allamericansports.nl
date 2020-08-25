<?php
class ProductTypeDao {
    public static function getLaderTypes(){
        return array('Lader','Originele lader');
    }
    public static function getAccuTypes(){
        return array('Accu','Originele accu');
    }
    public static function getTypes($selectedType){
        if(strtolower($selectedType)=='adapter' || strtolower($selectedType)=='adapters'){
            return self::getLaderTypes();                    
        }else{            
            return self::getAccuTypes();                        
        }                      
    }
    public static function getAllTranslated(){                
        $result = fetchArray($sql = sprintf('SELECT                                            
                                            pt.id type_id,
                                            pt.`type` typelabel,
                                            l.locale,
                                            l.id locale_id,
                                            ptt.type translated_type,                                            
                                            pt.*                                            
                                          FROM                                            
                                            locales l,
                                            product_type pt                                            
                                            LEFT JOIN webshop_translations wt  ON 1=1                                                                                      
                                            LEFT JOIN product_type_translation ptt ON ptt.fk_product_type=pt.id AND wt.fk_locale=ptt.fk_locale
                                          WHERE 
                                            l.id = wt.fk_locale                                             
                                          GROUP BY
                                            pt.`type`,wt.fk_locale
                                          ORDER BY
                                            pt.`type`'),__METHOD__);    
        #echo "<br><br>".nl2br($sql)."<br><br>";
        foreach($result as $id=>$row){            
            $out[$row['type_id']]['label'] = $row['typelabel'];
            $out[$row['type_id']]['items'][] = $row;
        }        
        return $out;
    }
            
    public static function getAll(){
        $out = fetchArray($sql = sprintf('SELECT
                                            pt.*,
                                            COUNT(c.id) quantity
                                          FROM
                                            product_type pt
                                            LEFT JOIN catalogue c ON pt.id=c.`type`
                                          GROUP BY
                                            pt.`type`
                                          ORDER BY
                                            pt.`type`'),__METHOD__);
    
        foreach($out as $id=>$row){
            $out[$id]['type_encoded'] = urlencode($row['type']);
        }
        return $out;
    }
    public static function add($name){
       // Clear cache
       unset($_SESSION['webshop_product_types']);
       query(sprintf('INSERT INTO product_type (`type`) VALUE("%s")',addslashes($name)),__METHOD__);
    }
    /**
     *
     * @param <type> $webshopId
     * @param <type> $typeId
     * @param <type> $visible 1,0 or _default for "copy from _default"
     */
    public static function setWebshopVisibility($webshopId,$typeId,$visible){
        // Clear cache
        unset($_SESSION['webshop_product_types']);
        if($visible!='_default'){
            $data = $keyval = array('webshop_id'=>$webshopId,'product_type_id'=>$typeId);
            $data['visible'] = $visible;
            DB::instance()->store('webshop_product_types',$keyval,$data);
        }else
            query(sprintf('DELETE FROM webshop_product_types
                            WHERE webshop_id=%d AND product_type_id=%d',$webshopId,$typeId),__METHOD__);
    }
    public static function getWebshopProductTypes($shopName,$stripInvisible=false,$cacheResults=true){
        if(isset($_SESSION['webshop_product_types'][$shopName][($stripInvisible)?1:0]) && $cacheResults){
            return $_SESSION['webshop_product_types'][$shopName][($stripInvisible)?1:0];
        } else {
            $productTypes = self::getAll();

            $sql = sprintf('
                    SELECT
                        pt.id type_id,
                        wpt.visible,
                        w.hostname visibility_set_by,
                        pt.type,
                        IF(w.hostname="_default","0","1") prio
                    FROM
                        webshop_product_types wpt,
                        product_type pt,
                        webshops w
                    WHERE
                        w.id=wpt.webshop_id
                    AND w.hostname IN("_default","%s")
                    AND pt.id = wpt.product_type_id
                    ORDER BY pt.`type`, prio',$shopName);
            $data = fetchArray($sql,__METHOD__);

            foreach($data as $id=>$row){
                $out[$row['type_id']]['visible']            = $row['visible'];
                $out[$row['type_id']]['visibility_set_by']  = $row['visibility_set_by'];
                $out[$row['type_id']]['type']               = $row['type'];
                $out[$row['type_id']]['id']                 = $row['type_id'];
                $out[$row['type_id']]['type_encoded']       = urlencode($row['type']);
            }
            if($stripInvisible)
                foreach($out as $id=>$row)
                    if($row['visible']==0)
                        unset($out[$id]);

            $_SESSION['webshop_product_types'][$shopName][($stripInvisible)?1:0] = $out;
            return $out;
        }
    }

}
