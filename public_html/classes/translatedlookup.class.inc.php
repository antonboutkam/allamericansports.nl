<?php
class TranslatedLookup{
    public static function delete($table,$item_id){
        query("DELETE FROM $table WHERE id = $item_id");
        query("DELETE FROM {$table}_translation WHERE fk_{$table} = $item_id");
        
    }    
    public static function getTranslatedValue($table='product_size',$fk_locale,$fk_db_id){
        $sql = 'SELECT `type` FROM '.$table.'_translation WHERE fk_'.$table.'='.$fk_db_id;        
        return fetchVal($sql,__METHOD__);
    }
    public static function add($table='product_type',$add){
        #pre_r($add);
        $ins['fk_'.$table] = store($table,array('id'=>'new'),$add['product_type']);
        #pre_r($ins);
        
        foreach($add['translations'] as $fk_locale => $translation){
            $ins['fk_locale']   = quote($fk_locale);
            $ins['type']        = quote($translation);
            #pre_r($ins);            
            insert($table.'_translation',$ins);
        }
        #exit();
        return $ins['fk_'.$table];
    }  
    
    public static function getProductsInGroup($baseTable = 'product_size',$groupId,$locale,$activeProductId){
        $catalogue_col = str_replace('product_','fk_',$baseTable);
        
        $sql = sprintf('SELECT 
                            c.id current_product_id,
                            bt.*,                            
                            CASE                                 
                                WHEN bt.`type` = "XXXL" THEN 1
                                WHEN bt.`type` = "XXL" THEN 1
                                WHEN bt.`type` = "XL" THEN 1
                                WHEN bt.`type` = "X" THEN 1
                                WHEN bt.`type` = "L" THEN 1
                                WHEN bt.`type` = "M" THEN 1
                                WHEN bt.`type` = "S" THEN 1
                                WHEN bt.`type` = "XS" THEN 1
                                WHEN bt.`type` = "XXS" THEN 1
                            ELSE 0
                            END AS standard_sizes,
                            IF(c.id='.$activeProductId.',1,0) active,
                            CASE
                                WHEN bt.`type` = "XXXL" THEN 4000
                                WHEN bt.`type` = "XXL" THEN 5000 
                                WHEN bt.`type` = "XL" THEN 6000
                                WHEN bt.`type` = "X" THEN 7000                                
                                WHEN bt.`type` = "L" THEN 8000
                                WHEN bt.`type` = "M" THEN 9000
                                WHEN bt.`type` = "S" THEN 10000
                                WHEN bt.`type` = "XS" THEN 11000
                                WHEN bt.`type` = "XXS" THEN 12000
								
                                WHEN LOWER(bt.`type`) = "axl (adult xtra large)" THEN 4300
                                WHEN LOWER(bt.`type`) = "al (adult large)" THEN 4400
                                WHEN LOWER(bt.`type`) = "am (adult medium)" THEN 4500
                                WHEN LOWER(bt.`type`) = "as (adult small)" THEN 4600
                                WHEN LOWER(bt.`type`) = "axs (adult xtra small)" THEN 4700
                                WHEN LOWER(bt.`type`) = "cl (child large)" THEN 4800
                                WHEN LOWER(bt.`type`) = "cm (child medium)" THEN 4900
                                WHEN LOWER(bt.`type`) = "cs (child small)" THEN 5000
                            ELSE 0
                            END AS size_prio,                            
                            c.title,
                            cpg.fk_product_group gid,
                            c.id product_id
                        FROM
                            %1$s b,
                            %1$s_translation bt,
                            catalogue c,
                            catalogue_product_group cpg
                        WHERE
                            bt.fk_%1$s = b.id
                        AND bt.fk_locale=%2$d
                        AND cpg.fk_product_group=%4$d
						AND c.in_webshop = 1
						AND c.deleted IS NULL							
                        AND c.%3$s=b.id                        
                        AND cpg.fk_catalogue=c.id 
                        GROUP BY bt.`type`
                        ORDER BY size_prio ',$baseTable,$locale,$catalogue_col,$groupId);
        
        $out = fetchArray($sql,__METHOD__);
        
        if(!empty($out)){
            foreach($out as $id=>$row){
              $out[$id]['title_encoded'] = stripSpecial($row['title']);              
              if($activeProductId==$row['current_product_id'])
                $out[$id]['active'] = 'active';                                
            }
        }
        #pre_r($out);
        #echo nl2br($sql);
        #exit();
        return $out;
    }
    
    /**
     * TranslatedLookup::getDropDown()
     * 
     * @param string $baseTable [product_size|product_type]
     * @return arrray
     */
    public static function getDropDownTags($baseTable = 'product_type'){
        $sql = "SELECT * FROM $baseTable";
        $out = fetchArray($sql,__METHOD__);
        
        return $out;        
    }
    public static function storeChanges($table,$translated){
        if(empty($translated))
            return;
        foreach($translated as $productTypeId=>$translations){
            foreach($translations as $fk_locale=>$translation){
                if(Lang::getLocaleIdByLanguageCode('nl')==$fk_locale){
                    $store_type = $translation;
                }
                $values[] = sprintf('(%d,%d,"%s")',$productTypeId,$fk_locale,quote($translation));
            }            
        }        
        
        $sql = 'INSERT INTO '.$table.'_translation 
                    (fk_'.$table.', fk_locale,type) VALUES'.join(',',$values).
                    'ON DUPLICATE KEY UPDATE type=VALUES(type)';
        #mail('anton@nui-boutkam.nl','XXX',$sql);                                    
        query($sql,__METHOD__);               
    }    
    public static function getAllTranslated($baseTable = 'product_type'){                
        $sql = "SELECT                                            
                            pt.id type_id,
                            pt.`type` typelabel,
                            l.locale,
                            l.id locale_id,
                            ptt.type translated_type,                                            
                            pt.*                                            
                          FROM                                            
                            locales l,
                            $baseTable pt                                            
                            LEFT JOIN webshop_translations wt  ON 1 = 1                                                                                      
                            LEFT JOIN {$baseTable}_translation ptt ON ptt.fk_{$baseTable} = pt.id AND wt.fk_locale = ptt.fk_locale
                          WHERE 
                            l.id = wt.fk_locale                                             
                          GROUP BY
                            pt.`type`,wt.fk_locale
                          ORDER BY
                            pt.`type`";
        #echo nl2br($sql);
        $result = fetchArray($sql,__METHOD__);
        foreach($result as $id=>$row){            
            $out[$row['type_id']]['label'] = $row['typelabel'];
            $out[$row['type_id']]['type_id'] = $row['type_id'];
            $out[$row['type_id']]['items'][] = $row;
        }        
        return $out;
    }    
    
}