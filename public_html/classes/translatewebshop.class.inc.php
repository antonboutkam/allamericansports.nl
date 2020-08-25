<?php
class TranslateWebshop{
    static function duplicate($srcId,$dstId){
        if($dstId){
            query('CREATE TEMPORARY TABLE catalogue_translation_tmp LIKE catalogue_translation',__METHOD__);
            $sql2 = sprintf('INSERT INTO catalogue_translation_tmp (fk_catalogue,fk_webshop,fk_locale,meta_title,meta_description,meta_keyword,title,description) SELECT '.$dstId.',fk_webshop,fk_locale,meta_title,meta_description,meta_keyword,title,description FROM catalogue_translation WHERE fk_catalogue=%d',$srcId);
            query($sql2,__METHOD__);
            #query('UPDATE catalogue_translation_tmp SET fk_catalogue='.$dstId,__METHOD__);        
            query('INSERT INTO catalogue_translation (fk_catalogue,fk_webshop,fk_locale,meta_title,meta_description,meta_keyword,title,description) SELECT fk_catalogue,fk_webshop,fk_locale,meta_title,meta_description,meta_keyword,title,description FROM catalogue_translation_tmp',__METHOD__);        
        }
    }
    static function getAllEnabledLanguages(){
        $sql    = 'SELECT 
                        l.description,
                        l.id,
                        l.locale                     
                    FROM 
                        webshop_translations wt,
                        locales l
                    WHERE 
                        l.id = wt.fk_locale
                    GROUP BY 
                    wt.fk_locale';
        $data   = fetchArray($sql,__METHOD__);
        return $data;            
    }    
    static function getAllWebshopsLocales(){
        $sql = 'SELECT 
                * 
                FROM 
                    webshop_translations wt,
                    locales l
                WHERE 
                    wt.fk_locale = l.id
                GROUP BY l.id';        
        return fetchArray($sql, __METHOD__);
    }
    static function store($product_id,$catalogue_translation){
        
        if(!empty($catalogue_translation)){
            $sql = sprintf('SELECT * FROM webshop_translations WHERE id IN(%s)',join(',',array_keys($catalogue_translation)));            
            $all = fetchArray($sql,__METHOD__);
            if(!empty($all)){
                foreach($all as $item){
                    $catalogue_translation[$item['id']] = array_merge($catalogue_translation[$item['id']],$item);
                }
            }
        }
        if(!empty($catalogue_translation))
            foreach($catalogue_translation as $id=>$transl){
                $currId = fetchVal(sprintf('SELECT id FROM catalogue_translation WHERE fk_catalogue=%d AND fk_locale=%d AND fk_webshop=%d',
                                $product_id,
                                $transl['fk_locale'],
                                $transl['fk_webshop']),__METHOD__);
                if($currId){
                    $tpl = 'UPDATE catalogue_translation SET meta_title="%s", meta_description="%s",meta_keyword="%s",title="%s",description="%s" WHERE id=%d';
                    $sql = sprintf($tpl,
                                quote($transl['meta_title']),
                                quote($transl['meta_description']),
                                quote($transl['meta_keyword']),
                                quote($transl['title']),
                                quote($transl['description']),
                                $currId);                    
                    query($sql,__METHOD__);                                
                }else{                    
                    $transl['fk_catalogue'] = $product_id;                    
                    unset($transl['id']);
                    insert('catalogue_translation',$transl);
                }                           
                
            }        
    }
    static function setDefaultLocale($localeId,$webshopId){
        queryf('UPDATE webshop_translations SET is_default=0 WHERE fk_webshop=%d',$webshopId);
        queryf('UPDATE webshop_translations SET is_default=1 WHERE fk_webshop=%d AND fk_locale=%d',$webshopId,$localeId);   
    }
    static function getDefaultLocale($webshopId){
        $sql = sprintf('SELECT fk_locale FROM webshop_translations WHERE is_default=1 AND fk_webshop=%d',$webshopId);
        return fetchVal($sql,__METHOD__);
    }
    
    static function setLocales($webshopId,$locations){
        queryf('DELETE FROM webshop_translations WHERE fk_webshop = %d',$webshopId);        
        if(!empty($locations) && is_array($locations))
            foreach($locations as $locationId=>$location){
                insert('webshop_translations',array('fk_webshop'=>$webshopId,'fk_locale'=>$locationId));
            }
                        
    }
    /**
     * TranslateWebshop::getAllLocales()
     * Retuns all locales that are available inside the system and checks for each locale if it is availbale in the webhop. 
     * @param mixed $webshopId
     * @return
     */
    function getAllLocales($webshopId){
        $sql = sprintf('SELECT l.*,
                                IF(wt.id IS NULL,0,1) available 
                        FROM 
                            locales l
                            LEFT JOIN webshop_translations wt ON wt.fk_locale = l.id AND wt.fk_webshop=%d                                                                                
                        GROUP BY l.id
                        ORDER BY description',$webshopId);
        $data = fetchArray($sql,__METHOD__);
        #pre_r($data);
        return $data;
    }
    /**
     * TranslateWebshop::getAllLocales()
     * Retuns all locales that are available inside the system and checks for each locale if it is availbale in the webhop. 
     * @param mixed $webshopId
     * @return
     */
    static function getWebshopLocales($webshopId){
        $sql = sprintf('SELECT l.* ,wt.*                                
                        FROM 
                            locales l,
                            webshop_translations wt
                        WHERE
                            wt.fk_locale = l.id 
                        AND wt.fk_webshop=%d                                                                                                                                    
                        GROUP BY l.id
                        ORDER BY description',$webshopId);
        $data = fetchArray($sql,__METHOD__);        
        return $data;
    }   
    
   
    /**
     * TranslateWebshop::getAllLocales()
     * Retuns all locales that are available inside the system and checks for each locale if it is availbale in the webhop. 
     * @param mixed $webshopId
     * @return
     */
    static function getAllAvailableTranslations($productId=null){
        $sql = sprintf('SELECT 
                            ct.*,
                            ct.description description_txt,
                            l.*,
                            w.*,
                            wt.id translation_webshop_id,
                            wt.fk_webshop,
                            wt.fk_locale,                            
                            IF(l.locale LIKE "nl%%",1,0) locale_prio, -- first all Dutch locales
                            IF(w.hostname = "_default",0,1) site_prio -- first all Dutch locales                             
                        FROM 
                            locales l,
                            webshop_translations wt
                            LEFT JOIN catalogue_translation ct ON ct.fk_catalogue=%d AND ct.fk_webshop = wt.fk_webshop AND ct.fk_locale = wt.fk_locale,  
                            webshops w
                        WHERE 
                            wt.fk_locale = l.id
                        AND w.id = wt.fk_webshop                                                                                
                        ORDER BY locale_prio DESC, site_prio DESC',$productId);
        #echo nl2br($sql);                     
        $data = fetchArray($sql,__METHOD__);        
        #pre_r($data);
        return $data;
    }
    
    public static function getTranslatedProductInfo($productId,$langCode,$webshopId){        
        $sql = sprintf('SELECT * 
            FROM 
            catalogue_translation
            WHERE 
            fk_catalogue=%d
            AND fk_locale=%d
            AND fk_webshop=%d',
            $productId,$langCode,$webshopId);
        #echo nl2br($sql);
        return fetchRow($sql,__METHOD__);            
        
    }
    
            
}