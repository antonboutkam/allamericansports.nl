<?php
class ProductOptions {
    public static function add($productId,$optionOfId){
        DB::instance()->insert('product_option', array('product_id'=>$productId,'optionof_id'=>$optionOfId));
    }
    public static function getAllOptionsOf($productId){
        $sql = sprintf('SELECT 
                            *,                            
                            po.id link_id,
                            po.optionof_id linked_product_id,
                            pt.type type_label,
                            c.type type_id
                        FROM
                            product_option po,
                            catalogue c,
                            product_type pt
                        WHERE
                            po.optionof_id=c.id
                        AND po.product_id=%d
                        AND pt.id=c.type',$productId);

        return fetchArray($sql,__METHOD__);
    }
    public static function getAllByType($productId){
        $options = self::getAllOptionsOf($productId);

        foreach($options as $option)
            $out[$option['type_label']][] = $option;
        return $out;
    }
    public static function delete($linkId){
	query(sprintf('DELETE FROM product_option WHERE id=%d',$linkId),__METHOD__);
    }
}

