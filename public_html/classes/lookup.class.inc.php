<?php
class Lookup{
    public static function update($group,$newval,$id){
        $sql = sprintf('UPDATE lookups SET `value`="%2$s" WHERE id=%3$d AND `group`="%1$s"',
                quote($group),quote($newval),$id);                        
        query($sql,__METHOD__);        
    }
            
    public static function add($group,$value){
        $sql = sprintf('INSERT INTO lookups (`group`,`value`) VALUE ("%1$s","%2$s")',
                quote($group),quote($value));       
        query($sql,__METHOD__);     
    }
    public static function delete($group,$id){
        $sql = sprintf('UPDATE lookups SET is_deleted=1 WHERE id=%2$d AND `group`="%1$s"',quote($group),$id);            
        query($sql,__METHOD__);        
    }           
    public static function getItems($group,$currentPage=null,$itemsPP=null){
        $limit = '';
        if(!is_null($currentPage)){
            $start  = $currentPage*$itemsPP-$itemsPP;                
            $limit  = sprintf('LIMIT %1$d, %2$d',$start,$itemsPP);
        }
        $sql    = sprintf('SELECT SQL_CALC_FOUND_ROWS 
                                id, `value` edit_field,`value` field
                           FROM lookups 
                           WHERE `group`="%1$s"
                           AND is_deleted=0
                           ORDER BY `value`
                           %2$s',
                           $group,$limit);        
        #echo nl2br($sql);
        $out['data']        = fetchArray($sql,__METHOD__);
        $out['rowcount']    = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);        
        $out['pages']       = paginate($currentPage,$out['rowcount'],$itemsPP);
        return $out;        
    }    
	public static function getBrandIdByName($brand){	   
	   $cacheKey   = 'Lookup_getBrandIdByName'.$brand;
       $out        = GlobalCache::isCached($cacheKey);
       
       if(!$out){       
    	   $sql = sprintf('SELECT id
                            FROM 
                                lookups  
                            WHERE 
                             `group` = "brand" 
                            AND LOWER(value) = "%s"',
                            $brand);        
            
            $out = fetchVal($sql,__METHOD__);   
            GlobalCache::store($cacheKey,$out);
        }  
        return $out; 	
	}
}