<?php
class Weshopusp{    
    public static function getBy($field,$val){
            $sql = sprintf('SELECT *  FROM webshop_usp 
                            WHERE %s=%s LIMIT 1',
                    quote($field),
                    quote($val));
            return fetchRow($sql,__METHOD__);		
    }	
    public static function getById($id){
            $sql = sprintf('SELECT * FROM webshop_usp WHERE id=%d LIMIT 1',quote($id));
            return fetchRow($sql,__METHOD__);		
    }    
    
    /**
     * Weshopusp::find()
     * Zoek in de database en geef een array met data, paginering en rowcount terug.
     * In de filter array is de default operator een =, alle andere denkbare operators zijn uiteraard mogelijk.
     * @param mixed $filters array(1=>array(key'=>'searchfield','value'=>'searchdata','operator'=>'='))
     * @param mixed $orderBy
     * @param mixed $page
     * @param mixed $itemspp
     * @return
     */
    public static function store($usps){
        foreach($usps as $webshop_id=>$languages){
            foreach($languages as $language_id=>$translations){
                
                foreach($translations as $sort=>$value){
                    $sql = sprintf('SELECT id FROM webshop_usp WHERE fk_webshop=%d AND `sort`=%d',$webshop_id,$sort);
                    #echo nl2br($sql)."<br><br>";
                    $webshop_usp = fetchVal($sql, $method);                                        
                    if(!$webshop_usp){
                        $webshop_usp = query($sql = sprintf('INSERT INTO webshop_usp (fk_webshop,`sort`) VALUE(%d,%d)',$webshop_id,$sort),__METHOD__);                    
                    }
                    #echo nl2br($sql)."<br><br>";
                    $webshop_usp_translation_id = fetchVal($sql = sprintf('SELECT wut.id 
                                                            FROM 
                                                                webshop_usp_translation wut,
                                                                webshop_usp wup
                                                            WHERE 
                                                                wut.fk_webshop_usp=%d 
                                                            AND wup.id = wut.fk_webshop_usp
                                                            AND wup.fk_webshop = %d
                                                            AND wut.fk_language=%d',
                                                            $webshop_usp,
                                                            $webshop_id,
                                                            $language_id),__METHOD__); 
                    
                   
                    #echo nl2br($sql)."<br><br>";
                    if($webshop_usp_translation_id){
                       $sql = sprintf($sql = 'UPDATE webshop_usp_translation 
                                        SET usp="%s" WHERE fk_webshop_usp=%d AND fk_language=%d',$value,$webshop_usp,$language_id);
                    }else{
                       $sql = sprintf($sql = 'INSERT INTO 
                                        webshop_usp_translation 
                                        (fk_webshop_usp,fk_language,usp) 
                                        VALUE (%d,%d,"%s")',$webshop_usp,$language_id,$value);
                    }
                    #echo nl2br($sql)."<br><br>";  
                    query($sql,__METHOD__);
                    
                }
            }
        }
     
    }
    public static function getWebshopUsps($webshop_id,$language_code){
        $cacheKey = 'getwebshopusps'.$webshop_id.'_'.$language_code;
        
        if(!$cached = GlobalCache::isCached($cacheKey)){
            $sql = sprintf('SELECT * FROM 
                            webshop_usp wusp,
                            webshop_usp_translation wuspt,
                            locales l
                         WHERE 
                            wusp.fk_webshop=%d
                         AND wuspt.fk_language= l.id 
                         AND l.locale="%s"         
                         AND usp!=""            
                         AND wuspt.fk_webshop_usp=wusp.id
                         ORDER BY wusp.`sort`',                
                    $webshop_id,
                    $language_code);           
            $cached = fetchArray($sql,__METHOD__);
            GlobalCache::store($cacheKey, $cached);
        }
        return $cached;        
    }
    public static function getLanguages($count,$webshop_id){
            $sql = sprintf('SELECT 
                                SQL_CALC_FOUND_ROWS 
                                *,
                                l.id fk_language
                            FROM
                                webshops w,
                                webshop_translations wt,
                                locales l                                
                            WHERE
                                w.id=wt.fk_webshop
                                AND wt.fk_locale = l.id
                                AND w.id=%d',$webshop_id
                    );

        $result['data']     = fetchArray($sql,__METHOD__);
       
        foreach($result['data'] as $id=> $row){
            #pre_r($row);
            foreach(range(1,$count) as $sort){
                $uspQ = sprintf(
                        "SELECT * FROM 
                            webshop_usp wusp,
                            webshop_usp_translation wuspt
                         WHERE 
                            wusp.fk_webshop=%d
                         AND wuspt.fk_language=%d
                         AND wuspt.fk_webshop_usp=wusp.id
                         AND wusp.`sort`=%d",$row['fk_webshop'],$row['fk_language'],$sort);
                #echo nl2br($uspQ)."<br><br>";
                $usps = fetchRow($uspQ,__METHOD__);
                if(empty($usps['usp']))                   
                    $usps['usp'] = ' ';
                
                $usps['sort'] = $sort;//for empty rows                
                $result['data'][$id]['usps'][$sort] = $usps;                                
            }
        }
        #LEFT JOIN webshop_usp wusp ON wusp.fk_webshop=wt.fk_webshop
        #LEFT JOIN webshop_usp_translation wuspt ON wuspt.fk_webshop_usp=wusp.id,
        #pre_r($result);
        return $result;
    }		

}