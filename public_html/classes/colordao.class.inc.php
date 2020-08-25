<?php
class ColorDao{

    public static function getProductColors($product_id){
        $sql = sprintf('SELECT
                            cc.*, 
                            c.color 
                        FROM catalogue_color cc, colors c 
                        WHERE c.id=cc.fk_color 
                        AND cc.fk_catalogue=%d
                        ORDER BY c.color',$product_id);
        #echo nl2br($sql);                        
        return fetchArray($sql,__METHOD__);                        
    }
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
    public static function addProductColor($product_id,$color_id){
        $dat = array('fk_color'=>$color_id,'fk_catalogue'=>$product_id);
        store('catalogue_color',array('id'=>'new'),$dat);
    }
    public static function removeColor($id,$color_id){
        query(sprintf('DELETE FROM catalogue_color WHERE fk_color=%d AND fk_catalogue=%d',$color_id,$id),__METHOD__);
    }
    
    
    public static function getUnused($product_id){
        $sql = sprintf('SELECT c.* 
                            FROM 
                            colors c
                            LEFT JOIN catalogue_color cc ON cc.fk_catalogue=%d AND cc.fk_color = c.id
                            WHERE   
                                cc.id IS NULL
                            ORDER BY c.color',$product_id);
        #echo nl2br($sql);
        $out = fetchArray($sql,__METHOD__);
        if(!empty($out)){
            foreach($out as $id => $aRow){
                $out[$id]['type_encoded'] = urlencode($aRow['color']);
            }
        }
        return $out;
    }    
    public static function getAll(){
        $out = fetchArray($sql = sprintf('SELECT * FROM colors ORDER BY color'),__METHOD__);
        if(!empty($out))
            foreach($out as $id=>$row)
                $out[$id]['type_encoded'] = urlencode($row['type']);    
        return $out;
    }
    public static function add($color){
       query(sprintf('INSERT INTO colors (`color`) VALUE("%s")',addslashes($color)),__METHOD__);
    }    
    
}