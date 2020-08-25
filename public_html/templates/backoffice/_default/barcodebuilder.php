<?php
class Barcodebuilder{    
     function  run($params){
        $params['barcode_paper'] = BarcodeDao::getAllLabelTypes();
        $params['rows'] = range(1,9);
        $params['cols'] = range(0,5);             
        $params['rand'] = rand(0,9999999999999);   
        return $params;            
    }
}