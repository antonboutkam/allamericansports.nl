<?php
class BarcodeDao{
    public static function getAllLabelTypes(){
        return fetchArray('SELECT * FROM barcode_paper',__METHOD__);
    }
    public static function getLabelSettings($id){
        return fetchRow(sprintf('SELECT * FROM barcode_paper WHERE id=%d',$id), __METHOD__);
    }
    public static function storeProductProps($barcodes,$produtid){
        foreach($barcodes as $sequence=>$barcode){
            $sql = sprintf('INSERT INTO product_barcodes
                            (product_id,sequence,barcode)
                            VALUES (%1$d,%2$d,"%3$s")
                            ON DUPLICATE KEY UPDATE
                            barcode="%3$s"',
                            $produtid,$sequence,$barcode);

            query($sql,__METHOD__);
        }
    }
    public static function getProductIdByBarcode($code){
        if(strpos($code,'ARTN')===0){
            return str_replace('ARTN','',$code);
        }else{
            $sql = sprintf('SELECT product_id FROM product_barcodes WHERE barcode="%s"',$code);
            return fetchVal($sql,__METHOD__);
        }
    }
    public static function getProductProps($params, $productid){
        $sql    = sprintf('SELECT * FROM product_barcodes WHERE product_id=%d',$productid);
        $data   = fetchArray($sql,__METHOD__);
        foreach($data as $row)
            $params['barcode_'.$row['sequence']] = $row['barcode'];
        return $params;
    }
}