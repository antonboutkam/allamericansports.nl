<?php
class Stock{
    function run($params){
        $oExactApi = ExactHandleOath::handle($_SERVER['REQUEST_URI']);

        $oExactStock = new ExactStock($oExactApi, Cfg::get('EXACT_DIVISION'));
        $iExactStock = $oExactStock->getStock($params['product_id']);

        if(is_numeric($iExactStock)){
            ExactStock::storeExactStockLocally($params['product_id'], $iExactStock);
        }

        echo $iExactStock;

        exit();
    }
}