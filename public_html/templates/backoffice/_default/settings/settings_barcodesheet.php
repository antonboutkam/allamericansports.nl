<?php
class Settings_barcodesheet{
    function  run($params){
        if($params['_do']=='save_sheet'){
            parse_str($params['data'], $data);
            $id  = $data['id'];
            unset($data['id']);            
            $params['id'] = DB::instance()->store('barcode_paper', array('id'=>$id), $data);
        }
        $params['randomproduct']    = fetchVal('SELECT id FROM catalogue ORDER BY RAND() LIMIT 1',__METHOD__);
        if($params['id'])
            $data = BarcodeDao::getLabelSettings($params['id']);
        if(is_array($data))
            $params = array_merge($data,$params);
        return $params;
    }
}