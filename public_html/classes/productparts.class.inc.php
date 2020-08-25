<?php
class ProductParts {
    public static function add($productId,$partOfId){
        DB::instance()->insert('product_part', array('product_id'=>$productId,'partof_id'=>$partOfId));
    }
    public static function getAllPartsOf($productId){

        $sql = sprintf('SELECT
                            c.*,
                            c.sale_price * 1.'.Cfg::getPref('btw').' sale_price_vat,
                            pp.id link_id,
                            pp.partof_id linked_product_id,
                            pt.type product_type
                            FROM
                            product_part pp,
                            catalogue c,
                            product_type pt
                            WHERE
                            pp.product_id =c.id
                            AND pp.partof_id=%d
                            AND pt.id=c.type
                            ORDER BY pt.type, c.article_name',$productId);
        
        $data = fetchArray($sql,__METHOD__);
        foreach($data as $key=>$row){
            $data[$key]['sale_price_vat_vis'] = number_format($data[$key]['sale_price_vat'],2,",",".");
        }
        return $data;
    }
    
     public static function getAllPartsFor($productId){
        $sql = sprintf('SELECT
                            c.*,
                            pp.id link_id,
                            pp.partof_id linked_product_id,
                            pt.type product_type
                        FROM
                            product_part pp,
                            catalogue c,
                            product_type pt
                        WHERE
                            pp.product_id =c.id
                        AND pt.id = c.type
                        AND pp.product_id=%d',$productId);        
        return fetchArray($sql,__METHOD__);
    }
    /**
     * Returns all products that have parts and are on stock
     */
    public static function getProductsWithPartsAndStock(){
        $sql = sprintf('SELECT
                            *
                        FROM
                        catalogue c,
                        product_part pp
                        
                        WHERE
                        pp.partof_id = c.id
                        
                        GROUP BY pp.partof_id
                        ');                
        $data = fetchArray($sql,__METHOD__);
        return urlEncodeFieldsInArray($data,array('article_name'));
    }


     public static function isPartsOf($productId){
        $sql = sprintf('SELECT *,
                            pp.id link_id,
                            pp.product_id linked_product_id
                        FROM
                            product_part pp,
                            catalogue c
                        WHERE
                            pp.partof_id =%d
                        AND pp.product_id=c.id',$productId);
        return fetchArray($sql,__METHOD__);
    }


    public static function delete($linkId){
	query(sprintf('DELETE FROM product_part WHERE id=%d',$linkId),__METHOD__);
    }
}

