<?php
class ProductUsageDao{
    public static function getProductColorsCommaSeparated($product_id){
        $sql = sprintf('SELECT
                            GROUP_CONCAT(pu.type) types                             
                        FROM 
                            catalogue_usage cu, 
                            product_usage pu 
                        WHERE pu.id=cu.fk_usage  
                        AND cu.fk_catalogue=%d
                        ORDER BY pu.type',$product_id);
        #echo nl2br($sql);                        
        return str_replace(',',', ',fetchVal($sql,__METHOD__));        
    }
    public static function getAll(){
        $out = fetchArray($sql = sprintf('SELECT * FROM product_usage ORDER BY `type`'),__METHOD__);
        if(!empty($out))
            foreach($out as $id=>$row)
                $out[$id]['type_encoded'] = urlencode($row['type']);    
        return $out;
    }
    public static function add($usage){
       query(sprintf('INSERT INTO product_usage (`type`) VALUE("%s")',addslashes($usage)),__METHOD__);
    }       
    
    public static function removeUsage($id,$usage_id){
        $sql = sprintf('DELETE FROM catalogue_usage WHERE fk_usage=%d AND fk_catalogue=%d',$usage_id,$id);
        #echo $sql;
        query($sql,__METHOD__);
    }
        
    public static function addProductUsage($product_id,$usage_id){
        $dat = array('fk_usage'=>$usage_id,'fk_catalogue'=>$product_id);
        store('catalogue_usage',array('id'=>'new'),$dat);
    }    
    public static function getUnused($product_id){
        $sql = sprintf('SELECT pu.* 
                            FROM 
                            product_usage pu
                            LEFT JOIN catalogue_usage cu ON cu.fk_catalogue=%d AND cu.fk_usage = pu.id
                            WHERE   
                                cu.id IS NULL
                            ORDER BY cu.fk_usage',$product_id);
        #echo nl2br($sql);
        $out = fetchArray($sql,__METHOD__);
        if(!empty($out))
            foreach($out as $id=>$row)
                $out[$id]['type_encoded'] = urlencode($row['type']);    
        return $out;
    }  
    public static function getProductUsages($product_id){
        $sql = sprintf('SELECT
                            cu.*, 
                            pu.type
                        FROM 
                            catalogue_usage cu, 
                            product_usage pu
                        WHERE pu.id=cu.fk_usage
                        AND cu.fk_catalogue=%d
                        ORDER BY pu.type',$product_id);
        #echo nl2br($sql);                        
        return fetchArray($sql,__METHOD__);                        
    }    
    /*

    public static function getProductColorsCommaSeparated($product_id){
        $sql = sprintf('SELECT
                            GROUP_CONCAT(c.color) colors                             
                        FROM catalogue_color cc, colors c 
                        WHERE c.id=cc.fk_color 
                        AND cc.fk_catalogue=%d
                        ORDER BY c.color',$product_id);
        #echo nl2br($sql);                        
        return fetchVal($sql,__METHOD__);        
    }

  
    */
}