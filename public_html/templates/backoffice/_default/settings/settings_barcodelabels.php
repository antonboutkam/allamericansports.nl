<?php
class Settings_barcodelabels{
    function  run($params){
        if($params['_do']=='delete')
            query(sprintf('DELETE FROM barcode_paper WHERE id=%d',$params['id']),__METHOD__);
        
        $params['barcodes']         =  BarcodeDao::getAllLabelTypes();
        $params['barcode_tbl']      = parse('inc/barcode_tbl',$params);
        return $params;
    }
}