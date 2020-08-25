<?php
class Products_Edit_Exactonline extends Products_edit_abstract{
		
	public function _doFirst($params){

        if($params['_do']=='get_exact_stock') {
            $oExactApi = ExactHandleOath::handle($_SERVER['REQUEST_URI']);

            $oExactStock = new ExactStock($oExactApi, Cfg::get('EXACT_DIVISION'));
            $iExactStock = $oExactStock->getStock($params['id']);

            $product['exact_lastcheck'] = ProductDao::getProductPropBy('id', $params['id'], 'exact_lastcheck');
            $product['exact']['stock'] = $iExactStock;
            exit(json_encode($product));
        }

        return $params;

	}
    public function _doBeforeSave($params){  
        $params['product']['exact_synced'] = '0';        
        return $params;
    }
    public function _doAfterSave($params){

        // ExactProduct::upload($params['id']);
        return $params;        
    }
	public function addContents($params){
        if(!isset($params['plugins'])){
            $params['plugins'] = '';
        }
        $params['exact_synced'] = '1';

        $params['plugins'] .= parse('products_edit_exactonline', $params, __FILE__);

		return $params;
	}

}